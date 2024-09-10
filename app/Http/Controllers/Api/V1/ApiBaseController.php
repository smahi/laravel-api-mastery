<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ApiBaseController extends Controller
{
    protected $policyClass;

    public function isAble($ability, $targetModel)
    {
        return Gate::authorize($ability, [$targetModel, $this->policyClass]);
    }
    public function include(string $relationship)
    {
        $param = request()->get('include');

        if (!isset($param)) {
            return false;
        }

        $includeValues = explode(',', strtolower($param));

        return in_array(strtolower($relationship), $includeValues);
    }
}
