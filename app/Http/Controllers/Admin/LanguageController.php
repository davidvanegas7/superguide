<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function index(): View
    {
        $languages = Language::withCount('courses')->orderBy('sort_order')->get();
        return view('admin.languages.index', compact('languages'));
    }

    public function create(): View
    {
        return view('admin.languages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'color'       => 'required|string|max:7',
            'icon'        => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'sort_order'  => 'integer',
        ]);
        $data['slug']   = Str::slug($data['name']);
        $data['active'] = $request->boolean('active', true);
        Language::create($data);
        return redirect()->route('admin.languages.index')->with('success', 'Lenguaje creado.');
    }

    public function edit(Language $language): View
    {
        return view('admin.languages.edit', compact('language'));
    }

    public function update(Request $request, Language $language): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'color'       => 'required|string|max:7',
            'icon'        => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'sort_order'  => 'integer',
        ]);
        $data['slug']   = Str::slug($data['name']);
        $data['active'] = $request->boolean('active', true);
        $language->update($data);
        return redirect()->route('admin.languages.index')->with('success', 'Lenguaje actualizado.');
    }

    public function destroy(Language $language): RedirectResponse
    {
        $language->delete();
        return redirect()->route('admin.languages.index')->with('success', 'Lenguaje eliminado.');
    }
}
