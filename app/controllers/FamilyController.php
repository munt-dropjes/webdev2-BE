<?php
namespace Controllers;

use Config\Database;
use Repositories\FamilyRepository;

class FamilyController {
    private FamilyRepository $repo;

    public function __construct() {
        $this->repo = new FamilyRepository(Database::getConnection());
    }

    /**
     * GET /api/families
     * Returns: [ { "id": 1, "name": "Haviken", "cash": 100000, "color": "#..." }, ... ]
     */
    public function index() {
        $families = $this->repo->findAll();

        // Ensure numeric types are actually numbers (PDO often returns strings)
        foreach ($families as &$family) {
            $family['cash'] = (float)$family['cash'];
            $family['id'] = (int)$family['id'];
        }

        header('Content-Type: application/json');
        echo json_encode($families);
    }

    /**
     * POST /api/transactions
     * Input: { "family_id": 1, "amount": 5000, "reason": "Won Task" }
     */
    public function transaction() {
        $input = json_decode(file_get_contents('php://input'), true);

        // Validation
        if (!isset($input['family_id'], $input['amount'], $input['reason'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields: family_id, amount, reason']);
            exit;
        }

        try {
            $this->repo->updateCash(
                (int)$input['family_id'],
                (float)$input['amount'],
                $input['reason']
            );

            http_response_code(201);
            echo json_encode(['message' => 'Transaction successful']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/history
     * Optional: Returns recent transactions for the graph/log
     */
    public function history() {
        $history = $this->repo->getTransactionHistory();
        header('Content-Type: application/json');
        echo json_encode($history);
    }
}
