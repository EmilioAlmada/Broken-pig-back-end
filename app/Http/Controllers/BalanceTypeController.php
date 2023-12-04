<?php

namespace App\Http\Controllers;

use App\Models\BalanceType;
use App\Models\EconomicProfile;
use Illuminate\Http\Request;

class BalanceTypeController extends Controller
{
    public function getBasicData(Request $request) {
        $balanceTypes = BalanceType::get();
        $economicProfiles = EconomicProfile::get();
        $basicData = array(
            "balanceTypes" => $balanceTypes,
            "economicProfiles" => $economicProfiles
        );
        return $this->successGet($basicData);
    }
}
