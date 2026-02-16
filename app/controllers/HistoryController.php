<?php

namespace Controllers;

use Exception;
use Services\HistoryService;
use Services\NpcService;

class HistoryController extends Controller
{
    private HistoryService  $historyService;
    private NpcService $npcService;

    public function __construct() {
        $this->historyService = new HistoryService();
        $this->npcService = new NpcService();
    }

    public function saveHistory() {
        try {
            $this->npcService->processTick();
            $result = $this->historyService->saveHistory();
            $this->respond($result);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function getHistorySince(string $dateTime) {
        try {
            $decodedDate = urldecode($dateTime);
            $history = $this->historyService->getHistorySince($decodedDate);
            $this->respond($history);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode() ?: 500, $e->getMessage());
        }
    }

}
