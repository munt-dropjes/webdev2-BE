<?php

namespace Controllers;

use Exception;
use Services\HistoryService;

class HistoryController extends Controller
{
    private HistoryService  $historyService;

    public function __construct() {
        $this->historyService = new HistoryService();
    }

    public function saveHistory() {
        try {
            $result = $this->historyService->saveHistory();
            $this->respond($result);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getHistorySince(string $dateTime) {
        try {
            $decodedDate = urldecode($dateTime);
            $history = $this->historyService->getHistorySince($decodedDate);
            $this->respond($history);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

}
