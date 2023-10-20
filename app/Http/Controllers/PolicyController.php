<?php

namespace App\Http\Controllers;

use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\StorePolicyRequest;
use App\Http\Resources\PolicyIndexResource;
use App\Http\Resources\PolicyShowResource;
use App\Models\Policy;

class PolicyController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('policy.access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $policies = Policy::all();

        return Inertia::render('Policy/Index', [
            'data' => [
                'policies' => PolicyIndexResource::collection($policies),
            ],
            'meta' => [

            ],
        ]);
    }

    public function show($id): Response
    {
        abort_if(Gate::denies('policy.view'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $policy = Policy::findOrFail($id)->load(['insurer', 'policyholder', 'agent']);

        return Inertia::render('Policy/Show', [
            'policy' => new PolicyShowResource($policy),
        ]);
    }

    public function create(Request $request)
    {
        abort_if(Gate::denies('policy.create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $categoryId = $request->query('categoryId');

        return Inertia::render('Policy/Create', [
            'data' => [
                'selectedCategory' => $categoryId,

                'select' => [
                    'coverageTypes' => Policy::COVERAGE_TYPE_SELECT,
                    'layers' => Policy::LAYER_SELECT,
                    'status' => Policy::STATUS_SELECT,
                ],
            ],
            'meta' => [

            ],
        ]);
    }

    public function store(StorePolicyRequest $request): RedirectResponse
    {
        $policy = new Policy($request->validated());

        return redirect()->route('policies.index')
            ->with('success', 'New Policy has been created.');
    }
}
