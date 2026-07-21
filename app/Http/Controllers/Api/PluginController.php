<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PluginController extends Controller
{
    public function index(): JsonResponse
    {
        $plugins = Plugin::query()
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Plugins retrieved successfully.',
            'data' => $plugins,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:plugins,slug'],
            'description' => ['nullable', 'string'],
            'version' => ['nullable', 'string', 'max:50'],
            'enabled' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
        ]);

        $plugin = Plugin::create($validated);

        return response()->json([
            'message' => 'Plugin created successfully.',
            'data' => $plugin,
        ], 201);
    }

    public function show(Plugin $plugin): JsonResponse
    {
        return response()->json([
            'message' => 'Plugin retrieved successfully.',
            'data' => $plugin,
        ]);
    }

    public function update(Request $request, Plugin $plugin): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('plugins', 'slug')->ignore($plugin->id),
            ],
            'description' => ['nullable', 'string'],
            'version' => ['sometimes', 'required', 'string', 'max:50'],
            'enabled' => ['sometimes', 'required', 'boolean'],
            'settings' => ['nullable', 'array'],
        ]);

        $plugin->update($validated);

        return response()->json([
            'message' => 'Plugin updated successfully.',
            'data' => $plugin->fresh(),
        ]);
    }

    public function destroy(Plugin $plugin): JsonResponse
    {
        $plugin->delete();

        return response()->json([
            'message' => 'Plugin deleted successfully.',
        ]);
    }
}