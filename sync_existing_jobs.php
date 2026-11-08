<?php
require_once 'c:\Users\JB\Documents\multitenant\database.php';

try {
    $db->beginTransaction();
    // Find all repair_jobs that have an appointment_id
    $stmt = $db->query("SELECT job_id, appointment_id, tenant_id FROM repair_jobs WHERE appointment_id IS NOT NULL");
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;
    foreach ($jobs as $job) {
        // Get the base appointment details
        $apptStmt = $db->prepare("SELECT customer_id, vehicle_id, appointment_date, appointment_time, service_id FROM appointments WHERE appointment_id = ?");
        $apptStmt->execute([$job['appointment_id']]);
        $baseAppt = $apptStmt->fetch(PDO::FETCH_ASSOC);

        if ($baseAppt) {
            // Find other appointments with the same grouping criteria (except the base one)
            $otherStmt = $db->prepare("SELECT a.service_id, s.price 
                                       FROM appointments a 
                                       JOIN services s ON a.service_id = s.service_id
                                       WHERE a.tenant_id = ? 
                                       AND a.customer_id = ? 
                                       AND a.vehicle_id = ? 
                                       AND a.appointment_date = ? 
                                       AND a.appointment_time = ? 
                                       AND a.appointment_id != ?");
            $otherStmt->execute([
                $job['tenant_id'],
                $baseAppt['customer_id'],
                $baseAppt['vehicle_id'],
                $baseAppt['appointment_date'],
                $baseAppt['appointment_time'],
                $job['appointment_id']
            ]);
            $otherAppts = $otherStmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($otherAppts) > 0) {
                foreach ($otherAppts as $oA) {
                    // Check if already in repair_parts
                    $checkPart = $db->prepare("SELECT COUNT(*) FROM repair_parts WHERE job_id = ? AND service_id = ?");
                    $checkPart->execute([$job['job_id'], $oA['service_id']]);
                    if ($checkPart->fetchColumn() == 0) {
                        $insertPart = $db->prepare("INSERT INTO repair_parts (tenant_id, job_id, service_id, quantity, unit_price, total_price) VALUES (?, ?, ?, 1, ?, ?)");
                        $insertPart->execute([
                            $job['tenant_id'],
                            $job['job_id'],
                            $oA['service_id'],
                            $oA['price'],
                            $oA['price']
                        ]);
                        $count++;
                    }
                }
                
                // Update total_amount
                $db->prepare("UPDATE repair_jobs j SET total_amount = (COALESCE((SELECT price FROM services WHERE service_id = j.service_id), 0) + COALESCE((SELECT SUM(total_price) FROM repair_parts WHERE job_id = j.job_id), 0)) WHERE j.job_id = ?")->execute([$job['job_id']]);
            }
        }
    }
    
    $db->commit();
    echo "Successfully synced $count missing services to existing jobs.";
} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
