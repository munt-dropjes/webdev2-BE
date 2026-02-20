<?php

namespace Controllers;

use Exception;
use Models\DTO\TradeOfferRequest;
use Services\AuthService;
use Services\TradeOfferService;

class TradeOfferController extends Controller
{
    private TradeOfferService $offerService;
    private AuthService $authService;

    public function __construct() {
        $this->offerService = new TradeOfferService();
        $this->authService = new AuthService();
    }

    public function create(): void
    {
        try {
            $user = $this->authService->getCurrentUserFromTokenPayload();
            if (!$user->company_id) {
                $this->respondWithError(403, "Alleen bedrijven kunnen een bod doen.");
            }

            $request = $this->requestObjectFromPostedJson(TradeOfferRequest::class);
            $this->offerService->createOffer($user->company_id, $request);
            $this->respond(["message" => "Bod succesvol verstuurd."]);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function getPending(): void
    {
        try {
            $user = $this->authService->getCurrentUserFromTokenPayload();
            if (!$user->company_id) {
                $this->respondWithError(403, "Alleen bedrijven kunnen biedingen bekijken.");
            }
            $offers = $this->offerService->getPendingOffers($user->company_id);
            $this->respond($offers);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function accept($id): void
    {
        try {
            $user = $this->authService->getCurrentUserFromTokenPayload();
            $this->offerService->acceptOffer($user->company_id, (int)$id);
            $this->respond(["message" => "Bod succesvol geaccepteerd."]);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function decline($id): void
    {
        try {
            $user = $this->authService->getCurrentUserFromTokenPayload();
            $this->offerService->declineOffer($user->company_id, (int)$id);
            $this->respond(["message" => "Bod afgewezen."]);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode() ?: 500, $e->getMessage());
        }
    }
}
