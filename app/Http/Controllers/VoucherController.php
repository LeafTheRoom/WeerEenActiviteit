<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    public function showActivationForm()
    {
        return view('vouchers.activate');
    }

    public function generateCode()
    {
        // Genereer een random code
        $code = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        
        // Maak de voucher aan
        $voucher = Voucher::create([
            'code' => $code,
            'duration_days' => 999999, // Lifetime
        ]);

        return view('vouchers.payment', compact('code'));
    }

    public function activate(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $voucher = Voucher::where('code', strtoupper($request->code))->first();

        if (!$voucher) {
            return back()->with('error', 'Deze code is niet geldig.');
        }

        if (!$voucher->isAvailable()) {
            return back()->with('error', 'Deze code is al gebruikt.');
        }

        if ($request->user()->is_premium) {
            return back()->with('error', 'Je hebt al een actief premium abonnement.');
        }

        $voucher->activate($request->user());

        return redirect()->route('dashboard')->with('success', 'Premium geactiveerd! Je hebt nu toegang tot alle functies.');
    }
}


