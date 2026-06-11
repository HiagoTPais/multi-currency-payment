<?php

namespace App\Http\Controllers\Api;

use App\Actions\ApprovePaymentRequestAction;
use App\Actions\CreatePaymentRequestAction;
use App\Actions\RejectPaymentRequestAction;
use App\Enums\PaymentRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequests\IndexPaymentRequestRequest;
use App\Http\Requests\PaymentRequests\RejectPaymentRequestRequest;
use App\Http\Requests\PaymentRequests\StorePaymentRequestRequest;
use App\Http\Resources\PaymentRequestResource;
use App\Models\PaymentRequest;
use App\Repositories\PaymentRequestRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class PaymentRequestController extends Controller
{
    public function index(IndexPaymentRequestRequest $request, PaymentRequestRepository $paymentRequests): AnonymousResourceCollection
    {
        $status = $request->validated('status')
            ? PaymentRequestStatus::from($request->validated('status'))
            : null;

        return PaymentRequestResource::collection(
            $paymentRequests->paginatedForUser($request->user('api'), $status),
        );
    }

    public function store(StorePaymentRequestRequest $request, CreatePaymentRequestAction $createPaymentRequest): JsonResponse
    {
        $paymentRequest = $createPaymentRequest->execute(
            user: $request->user('api'),
            amountLocal: (float) $request->validated('amount_local'),
            notes: $request->validated('notes'),
        );

        return (new PaymentRequestResource($paymentRequest))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(PaymentRequest $paymentRequest): PaymentRequestResource
    {
        $this->authorize('view', $paymentRequest);

        return new PaymentRequestResource(
            $paymentRequest->load(['user:id,name,email,currency_code', 'approver:id,name,email']),
        );
    }

    public function approve(PaymentRequest $paymentRequest, ApprovePaymentRequestAction $approvePaymentRequest): PaymentRequestResource
    {
        $this->authorize('approve', $paymentRequest);

        return new PaymentRequestResource(
            $approvePaymentRequest->execute($paymentRequest, request()->user('api')),
        );
    }

    public function reject(
        RejectPaymentRequestRequest $request,
        PaymentRequest $paymentRequest,
        RejectPaymentRequestAction $rejectPaymentRequest,
    ): PaymentRequestResource {
        $this->authorize('reject', $paymentRequest);

        return new PaymentRequestResource(
            $rejectPaymentRequest->execute(
                paymentRequest: $paymentRequest,
                rejector: $request->user('api'),
                notes: $request->validated('notes'),
            ),
        );
    }
}
