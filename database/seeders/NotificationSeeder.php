<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar notificaciones previas
        Notification::query()->delete();

        $notifications = [
            // --- MENSAJES ---
            [
                'user_id' => 1,
                'type'    => 'mensaje',
                'title'   => 'Nuevo mensaje de Juan Pérez',
                'message' => 'Hola administrador, ¿puede revisar el problema del agua en el piso 3?',
                'data'    => ['department_id' => 2, 'sender_name' => 'Juan Pérez'],
                'read'    => false,
            ],
            [
                'user_id' => 1,
                'type'    => 'mensaje',
                'title'   => 'Nuevo mensaje de María García',
                'message' => 'Buenos días, necesito un recibo de pago actualizado.',
                'data'    => ['department_id' => 3, 'sender_name' => 'María García'],
                'read'    => true,
            ],

            // --- MULTAS ---
            [
                'user_id' => 1,
                'type'    => 'multa',
                'title'   => 'Multa aplicada – Departamento 205',
                'message' => 'Se aplicó una multa por ruido excesivo después de las 22:00h. Monto: $500 MXN.',
                'data'    => ['department_id' => 4, 'monto' => 500, 'currency' => 'MXN', 'reglamento' => 'Art. 12'],
                'read'    => false,
            ],
            [
                'user_id' => 2,
                'type'    => 'multa',
                'title'   => 'Multa por estacionamiento',
                'message' => 'Su vehículo fue encontrado en zona de carga. Multa de $300 MXN pendiente de pago.',
                'data'    => ['department_id' => 2, 'monto' => 300, 'currency' => 'MXN', 'reglamento' => 'Art. 8'],
                'read'    => false,
            ],

            // --- ASAMBLEAS ---
            [
                'user_id' => 1,
                'type'    => 'asamblea',
                'title'   => 'Asamblea General – 15 de Febrero',
                'message' => 'Se convoca a asamblea general ordinaria de propietarios. Lugar: Salón de eventos. Hora: 18:00 hrs.',
                'data'    => [
                    'fecha'   => '2026-02-15',
                    'hora'    => '18:00',
                    'lugar'   => 'Salón de eventos, planta baja',
                    'agenda'  => ['Aprobación de presupuesto 2026', 'Elección de mesa directiva', 'Puntos varios'],
                ],
                'read'    => false,
            ],
            [
                'user_id' => 2,
                'type'    => 'asamblea',
                'title'   => 'Asamblea Extraordinaria – Mantenimiento',
                'message' => 'Se convoca asamblea extraordinaria para aprobar el contrato de mantenimiento del elevador.',
                'data'    => [
                    'fecha'   => '2026-02-20',
                    'hora'    => '10:00',
                    'lugar'   => 'Oficina de administración',
                    'agenda'  => ['Presentación de ofertas', 'Votación del contrato'],
                ],
                'read'    => true,
            ],

            // --- PAGOS ATRASADOS ---
            [
                'user_id' => 1,
                'type'    => 'pago_atrasado',
                'title'   => 'Pago atrasado – Departamento 301',
                'message' => 'El departamento 301 tiene un pago de cuota mensual pendiente desde enero 2026. Monto: $2,500 MXN.',
                'data'    => ['department_id' => 5, 'monto' => 2500, 'meses_atrasados' => 1, 'currency' => 'MXN'],
                'read'    => false,
            ],
            [
                'user_id' => 2,
                'type'    => 'pago_atrasado',
                'title'   => 'Recordatorio de pago',
                'message' => 'Su cuota de mantenimiento de febrero vence el 28 de este mes. Monto pendiente: $1,800 MXN.',
                'data'    => ['department_id' => 2, 'monto' => 1800, 'fecha_vencimiento' => '2026-02-28', 'currency' => 'MXN'],
                'read'    => false,
            ],
        ];

        foreach ($notifications as $data) {
            Notification::create($data);
        }

        $this->command->info('✅ Notificaciones de ejemplo creadas correctamente.');
    }
}
