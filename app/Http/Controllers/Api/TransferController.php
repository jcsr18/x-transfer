<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transfer\StoreTransferRequest;
use App\Repositories\UserRepository;
use App\Services\TransferService;
use App\Supports\NumericSupport;
use App\Supports\ResponseSupport;
use DB;
use Exception;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as StatusCode;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class TransferController extends Controller
{
    public function __construct(
        private readonly TransferService $transferService
    ) {}

    public function store(StoreTransferRequest $request)
    {
        try {
            $payload = $request->validated();

            return DB::transaction(function () use ($payload) {
                $userRepository = new UserRepository();
                $from = $userRepository->findByTransferKey($payload['from']);

                $lastTransaction = $userRepository->lastSenderTransaction($from);
                if (! is_null($lastTransaction) && $lastTransaction->created_at->addSeconds(3)->greaterThan(now())) {
                    throw new TooManyRequestsHttpException(message: 'Too many requests.');
                }

                $transaction = $this->transferService->localTransfer(
                    $from,
                    $userRepository->findByTransferKey($payload['to']),
                    NumericSupport::floatToCents($payload['amount']),
                );

                return Response::json($transaction, StatusCode::HTTP_CREATED);
            });
        } catch (Exception $exception) {
            return ResponseSupport::httpResponse($exception, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
