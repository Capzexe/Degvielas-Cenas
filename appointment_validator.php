<?php
$appointments = [
    [
        'customer' => 'Janis',
        'date' => '2026-09-16',
        'time' => '10:00',
        'service' => 'Oil change',
    ],
    [
        'customer' => 'Anna',
        'date' => '2026-09-16',
        'time' => '12:00',
        'service' => 'Diagnostics',
    ],
];

function canBookAppointment(array $appointments, string $date, string $time): bool
{
    foreach ($appointments as $appointment) {
    if ($appointment['date'] === $date && $appointment['time'] === $time) {
        return false;
    }
}

$dayOfWeek = date('N', strtotime($date));

if ($dayOfWeek >= 6) {
    return false;
}

return true;
}

var_dump(canBookAppointment($appointments, '2026-09-16', '10:00'));
var_dump(canBookAppointment($appointments, '2026-09-16', '11:00'));
var_dump(canBookAppointment($appointments, '2026-09-19', '11:00'));

?>