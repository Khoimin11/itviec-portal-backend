<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function industries(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['nullable', 'string', 'max:255']]);
        $name = $data['name'] ?? '';
        $industries = Industry::query()
            ->where(function ($query) use ($name): void {
                $query->where('name_en', 'like', '%'.$name.'%')->orWhere('name_vi', 'like', '%'.$name.'%');
            })->orderBy('name_en')->get();

        return response()->json(['isSuccess' => true, 'message' => '', 'data' => $industries]);
    }

    public function skills(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['nullable', 'string', 'max:255']]);
        $skills = Skill::where('name', 'like', '%'.($data['name'] ?? '').'%')->orderBy('name')->limit(100)->get();

        return response()->json(['isSuccess' => true, 'message' => '', 'data' => $skills]);
    }
}
