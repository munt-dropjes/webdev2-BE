<?php

namespace Services;

use Exception;
use Models\DTO\TradeOfferRequest;
use Repositories\TradeOfferRepository;
use Repositories\CompanyRepository;
use Repositories\StockRepository;

class TradeOfferService
{
    private TradeOfferRepository $offerRepo;
    private CompanyRepository $companyRepo;
    private StockRepository $stockRepo;

    public function __construct() {
        $this->offerRepo = new TradeOfferRepository();
        $this->companyRepo = new CompanyRepository();
        $this->stockRepo = new StockRepository();
    }

    /**
     * @throws Exception
     */
    public function createOffer(int $buyerId, TradeOfferRequest $request): void {
        if ($buyerId === $request->seller_id) {
            throw new Exception("Je kunt geen bod doen op jezelf.", 400);
        }
        if ($request->amount <= 0 || $request->total_price < 0) {
            throw new Exception("Ongeldig aantal of prijs.", 400);
        }

        $this->offerRepo->createOffer($buyerId, $request);
    }

    /**
     * @throws Exception
     */
    public function getPendingOffers(int $companyId): array {
        return $this->offerRepo->getPendingOffersForSeller($companyId);
    }

    /**
     * @throws Exception
     */
    public function acceptOffer(int $sellerId, int $offerId): void {
        $offer = $this->offerRepo->getById($offerId);

        if (!$offer || $offer->seller_id !== $sellerId) {
            throw new Exception("Bod niet gevonden of niet gemachtigd.", 404);
        }
        if ($offer->status !== 'pending') {
            throw new Exception("Dit bod is niet meer in afwachting.", 400);
        }

        // 1. JIT Check: Buyer Cash
        $buyer = $this->companyRepo->findById($offer->buyer_id);
        if (!$buyer || $buyer->cash < $offer->total_price) {
            $this->offerRepo->updateStatus($offerId, 'declined');
            throw new Exception("De koper heeft niet genoeg geld meer. Bod automatisch afgewezen.", 400);
        }

        // 2. JIT Check: Seller Shares
        $sellerShareAmount = $this->stockRepo->getShareAmount($offer->target_company_id, $sellerId);
        if ($sellerShareAmount < $offer->amount) {
            $this->offerRepo->updateStatus($offerId, 'declined');
            throw new Exception("Je hebt niet genoeg aandelen meer om te verkopen. Bod automatisch afgewezen.", 400);
        }

        $targetCompany = $this->companyRepo->findById($offer->target_company_id);
        $targetName = $targetCompany ? $targetCompany->name : "Onbekend";

        // 3. Descriptions for Ledger
        $buyerDesc = "Aankoop {$offer->amount} aandelen {$targetName} van bedrijf #{$sellerId}";
        $sellerDesc = "Verkoop {$offer->amount} aandelen {$targetName} aan bedrijf #{$offer->buyer_id}";

        // 4. Execute
        $this->offerRepo->executeAcceptOffer($offer, $buyerDesc, $sellerDesc);
    }

    /**
     * @throws Exception
     */
    public function declineOffer(int $sellerId, int $offerId): void {
        $offer = $this->offerRepo->getById($offerId);
        if (!$offer || $offer->seller_id !== $sellerId) {
            throw new Exception("Bod niet gevonden of niet gemachtigd.", 404);
        }
        $this->offerRepo->updateStatus($offerId, 'declined');
    }
}
