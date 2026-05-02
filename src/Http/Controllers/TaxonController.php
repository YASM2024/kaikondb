<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Kaikon2\Kaikondb\Models\Order;
use Kaikon2\Kaikondb\Models\Family;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;

class TaxonController extends Controller
{
    //
    public function showMaster(){
        return view('kaikon::masters.taxon');
    }

    public function upperTaxa(Request $request)
    {
        $validated = $request->validate([
            'order_id'  => ['nullable', 'integer', 'required_without:family_id', 'exists:orders,id'],
            'family_id' => ['nullable', 'integer', 'required_without:order_id', 'exists:families,id'],
        ]);

        $upperTaxa = [];

        if (!empty($validated['family_id'])) {
            $family = Family::select('id', 'order_id', 'family', 'family_ja')
                ->findOrFail($validated['family_id']);

            $upperTaxa['family'] = $family->only(['id', 'family', 'family_ja']);
            $orderId = $family->order_id;
        } else {
            $orderId = $validated['order_id'];
        }

        $order = Order::select('id', 'order', 'order_ja')->findOrFail($orderId);

        $upperTaxa['order'] = $order->only(['id', 'order', 'order_ja']);

        return response()->json($upperTaxa);
    }
}
