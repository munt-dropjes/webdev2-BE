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
            $request = $this->requestObjectFromPostedJson(TradeOfferRequest::class);

            $buyerId = $user->company_id;

            // Uitzondering voor de Admin:
            if ($user->role === 'admin') {
                if (!empty($request->buyer_id)) {
                    $buyerId = $request->buyer_id;
                } else {
                    $this->respondWithError(400, "Als admin moet je een 'buyer_id' meesturen om namens een bedrijf een bod te doen.");
                    return;
                }
            } elseif (!$buyerId) {
                $this->respondWithError(403, "Alleen bedrijven kunnen een bod doen.");
                return;
            }

            $this->offerService->createOffer($buyerId, $request);
            $this->respond(["message" => "Bod succesvol verstuurd."]);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function getPending(): void
    {
        try {
            $user = $this->authService->getCurrentUserFromTokenPayload();

            // Uitzondering voor de Admin:
            if ($user->role === 'admin') {
                $offers = $this->offerService->getAllPendingOffers();
            } else {
                if (!$user->company_id) {
                    $this->respondWithError(403, "Geen bedrijf gekoppeld aan dit account.");
                    return;
                }
                $offers = $this->offerService->getPendingOffers($user->company_id);
            }

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
