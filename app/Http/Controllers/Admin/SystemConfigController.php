<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class SystemConfigController extends Controller
{
    /**
     * Display the system configuration page.
     */
    public function index(): View
    {
        // Carregar configurações do banco
        $settings = [
            'app_name' => SystemSetting::get('app_name', 'FinanceTracker'),
            'logo' => SystemSetting::get('logo_path'),
            'favicon' => SystemSetting::get('favicon_path'),
            'primary_color' => SystemSetting::get('primary_color', '#4F46E5'),
            'logo_display_mode' => SystemSetting::get('logo_display_mode', 'logo_only'), // NOVO
        ];
        
        return view('admin.system-config.index', compact('settings'));
    }

    /**
     * Update system configuration.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
            'favicon' => ['nullable', 'mimes:ico,png', 'max:512'],
            'primary_color' => ['nullable', 'regex:/^#[0-9A-F]{6}$/i'],
            'logo_display_mode' => ['required', 'in:logo_only,logo_and_name'], // NOVO
            'remove_logo' => ['boolean'],
            'remove_favicon' => ['boolean'],
        ]);

        // Salvar modo de exibição
        SystemSetting::set('logo_display_mode', $validated['logo_display_mode']);

        // Atualizar nome da aplicação (só se modo = logo_and_name)
        if ($validated['logo_display_mode'] === 'logo_and_name' && $request->filled('app_name')) {
            SystemSetting::set('app_name', $validated['app_name']);
        }

        // Upload da logo
        if ($request->hasFile('logo')) {
            // Deletar logo antiga
            $oldLogo = SystemSetting::get('logo_path');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            
            // Salvar nova logo
            $path = $request->file('logo')->store('system', 'public');
            SystemSetting::set('logo_path', $path);
        }

        // Remover logo
        if ($request->boolean('remove_logo')) {
            $oldLogo = SystemSetting::get('logo_path');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            SystemSetting::set('logo_path', null);
        }

        // Upload do favicon
        if ($request->hasFile('favicon')) {
            // Deletar favicon antigo
            $oldFavicon = SystemSetting::get('favicon_path');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            
            // Salvar novo favicon
            $path = $request->file('favicon')->store('system', 'public');
            SystemSetting::set('favicon_path', $path);
        }

        // Remover favicon
        if ($request->boolean('remove_favicon')) {
            $oldFavicon = SystemSetting::get('favicon_path');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            SystemSetting::set('favicon_path', null);
        }

        // Atualizar cor primária
        if ($request->filled('primary_color')) {
            SystemSetting::set('primary_color', $validated['primary_color']);
        }

        // Limpar cache
        SystemSetting::clearCache();

        return back()->with('success', 'Configurações do sistema atualizadas com sucesso!');
    }
}