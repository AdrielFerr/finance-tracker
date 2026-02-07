<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        $paymentMethods = PaymentMethod::forUser($user->id)
            ->withCount('expenses')
            ->orderBy('name')
            ->get();

        return view('payment-methods.index', compact('paymentMethods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('payment-methods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:credit_card,debit_card,bank_slip,pix,cash,bank_transfer,other',
            'last_four_digits' => 'nullable|string|size:4',
            'expiry_date' => 'nullable|date',
            'due_day' => 'nullable|integer|min:1|max:31',
            'closing_day' => 'nullable|integer|min:1|max:31',
            'credit_limit' => 'nullable|numeric|min:0',
            'brand' => 'nullable|string|max:50',
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'type.required' => 'O tipo é obrigatório.',
            'type.in' => 'Tipo inválido.',
            'last_four_digits.size' => 'Devem ser exatamente 4 dígitos.',
            'due_day.between' => 'Dia de vencimento deve estar entre 1 e 31.',
            'closing_day.between' => 'Dia de fechamento deve estar entre 1 e 31.',
            'color.regex' => 'A cor deve estar no formato hexadecimal (#000000).',
        ]);

        $validated['user_id'] = $user->id;

        PaymentMethod::create($validated);

        return redirect()
            ->route('payment-methods.index')
            ->with('success', 'Método de pagamento criado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        // Verificar propriedade
        if ($paymentMethod->user_id !== Auth::id()) {
            abort(403);
        }

        return view('payment-methods.edit', compact('paymentMethod'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        // Verificar propriedade
        if ($paymentMethod->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:credit_card,debit_card,bank_slip,pix,cash,bank_transfer,other',
            'last_four_digits' => 'nullable|string|size:4',
            'expiry_date' => 'nullable|date',
            'due_day' => 'nullable|integer|min:1|max:31',
            'closing_day' => 'nullable|integer|min:1|max:31',
            'credit_limit' => 'nullable|numeric|min:0',
            'brand' => 'nullable|string|max:50',
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'is_active' => 'boolean',
        ]);

        $paymentMethod->update($validated);

        return redirect()
            ->route('payment-methods.index')
            ->with('success', 'Método de pagamento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        // Verificar propriedade
        if ($paymentMethod->user_id !== Auth::id()) {
            abort(403);
        }

        // Verificar se há despesas associadas
        if ($paymentMethod->expenses()->count() > 0) {
            return back()->with('error', 'Este método de pagamento não pode ser excluído pois possui despesas associadas.');
        }

        $paymentMethod->delete();

        return redirect()
            ->route('payment-methods.index')
            ->with('success', 'Método de pagamento excluído com sucesso!');
    }

    /**
     * Toggle status
     */
    public function toggleStatus(PaymentMethod $paymentMethod)
    {
        // Verificar propriedade
        if ($paymentMethod->user_id !== Auth::id()) {
            abort(403);
        }

        $paymentMethod->is_active = !$paymentMethod->is_active;
        $paymentMethod->save();

        $status = $paymentMethod->is_active ? 'ativado' : 'desativado';

        return back()->with('success', "Método de pagamento {$status} com sucesso!");
    }
}