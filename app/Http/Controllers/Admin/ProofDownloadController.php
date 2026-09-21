<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProofDownloadController extends Controller
{
    public function __invoke(Order $order): StreamedResponse
    {
        $payment = $order->payments()->latest()->first();

        abort_unless($payment?->proof_file_path, 404);
        abort_unless(Storage::disk('local')->exists($payment->proof_file_path), 404);

        return Storage::disk('local')->response($payment->proof_file_path);
    }
}