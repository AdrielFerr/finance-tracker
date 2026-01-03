<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        $categories = Category::forUser($user->id)
            ->withCount('expenses')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')->where(function ($query) use ($user) {
                    return $query->where('user_id', $user->id);
                }),
            ],
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'name.unique' => 'Você já possui uma categoria com este nome.',
            'color.required' => 'A cor é obrigatória.',
            'color.regex' => 'A cor deve estar no formato hexadecimal (#000000).',
        ]);

        $validated['user_id'] = $user->id;

        Category::create($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        // Verificar propriedade
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }

        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        // Verificar propriedade
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }

        $user = Auth::user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')->where(function ($query) use ($user) {
                    return $query->where('user_id', $user->id);
                })->ignore($category->id),
            ],
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Verificar propriedade
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }

        // Verificar se pode ser deletada
        if (!$category->canBeDeleted()) {
            return back()->with('error', 'Esta categoria não pode ser excluída pois possui despesas associadas.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria excluída com sucesso!');
    }

    /**
     * Toggle status da categoria
     */
    public function toggleStatus(Category $category)
    {
        // Verificar propriedade
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }

        $category->is_active = !$category->is_active;
        $category->save();

        $status = $category->is_active ? 'ativada' : 'desativada';

        return back()->with('success', "Categoria {$status} com sucesso!");
    }
}