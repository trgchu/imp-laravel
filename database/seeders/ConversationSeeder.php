<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        // ── Limpiar en orden correcto (foreign keys) ────────────────────────
        DB::table('messages')->delete();
        DB::table('conversation_user')->delete();
        DB::table('conversations')->delete();
        DB::table('users')->delete();

        // ── Usuarios ────────────────────────────────────────────────────────
        DB::table('users')->insert([
            [
                'id'         => 1,
                'name'       => 'Luna Stellaris',
                'email'      => 'user1@example.com',
                'password'   => Hash::make('password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id'         => 2,
                'name'       => 'Ástrid Selenov',
                'email'      => 'user2@example.com',
                'password'   => Hash::make('password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id'         => 3,
                'name'       => 'Orion Cráter',
                'email'      => 'user3@example.com',
                'password'   => Hash::make('password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Bot Selene — responde automáticamente
            [
                'id'         => 99,
                'name'       => 'Selene 🌙',
                'email'      => 'selene@luna.space',
                'password'   => Hash::make('selene-bot-secret-2024'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // ── Conversaciones (created_by es obligatorio en la migración) ──────
        DB::table('conversations')->insert([
            [
                'id'          => 1,
                'name'        => 'Fases de la Luna 🌙',
                'description' => 'Todo sobre los ciclos y fases lunares',
                'created_by'  => 1,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'id'          => 2,
                'name'        => 'Misiones Apolo 🚀',
                'description' => 'Historia de las misiones espaciales a la Luna',
                'created_by'  => 2,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'id'          => 3,
                'name'        => 'Astronomía Lunar 🔭',
                'description' => 'Datos científicos sobre nuestro satélite',
                'created_by'  => 3,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
        ]);

        // ── Tabla pivote conversation_user ──────────────────────────────────
        // Todos los usuarios (incluido el bot) en todas las conversaciones
        $pivotRows = [];
        foreach ([1, 2, 3] as $convId) {
            foreach ([1, 2, 3, 99] as $userId) {
                $pivotRows[] = [
                    'conversation_id' => $convId,
                    'user_id'         => $userId,
                    'created_at'      => Carbon::now(),
                    'updated_at'      => Carbon::now(),
                ];
            }
        }
        DB::table('conversation_user')->insert($pivotRows);

        // ── Mensajes (campo correcto: content) ──────────────────────────────
        $now = Carbon::now();
        DB::table('messages')->insert([

            // ── Conversación 1: Fases de la Luna ──────────────────────────
            [
                'conversation_id' => 1, 'user_id' => 1,
                'content'  => '¿Sabías que la Luna tarda 29.5 días en completar un ciclo de fases? Se llama mes sinódico 🌑🌕',
                'created_at' => $now->copy()->subMinutes(60), 'updated_at' => $now->copy()->subMinutes(60),
            ],
            [
                'conversation_id' => 1, 'user_id' => 2,
                'content'  => 'Sí, y siempre vemos la misma cara desde la Tierra. Se llama rotación sincrónica 🌒',
                'created_at' => $now->copy()->subMinutes(58), 'updated_at' => $now->copy()->subMinutes(58),
            ],
            [
                'conversation_id' => 1, 'user_id' => 99,
                'content'  => 'La Luna tiene 8 fases: nueva, creciente iluminada, cuarto creciente, gibosa creciente, llena, gibosa menguante, cuarto menguante y creciente menguante 🌑🌒🌓🌔🌕🌖🌗🌘',
                'created_at' => $now->copy()->subMinutes(57), 'updated_at' => $now->copy()->subMinutes(57),
            ],
            [
                'conversation_id' => 1, 'user_id' => 3,
                'content'  => 'Me encanta la Luna llena. Los antiguos la usaban como calendario 🌕',
                'created_at' => $now->copy()->subMinutes(50), 'updated_at' => $now->copy()->subMinutes(50),
            ],
            [
                'conversation_id' => 1, 'user_id' => 2,
                'content'  => '¿Y saben qué es una Superluna? Cuando la luna llena coincide con el perigeo. ¡Se ve 14% más grande! 🌕✨',
                'created_at' => $now->copy()->subMinutes(45), 'updated_at' => $now->copy()->subMinutes(45),
            ],
            [
                'conversation_id' => 1, 'user_id' => 99,
                'content'  => 'Las mareas también cambian con las fases. En luna nueva y llena hay mareas vivas, más extremas 🌊',
                'created_at' => $now->copy()->subMinutes(44), 'updated_at' => $now->copy()->subMinutes(44),
            ],

            // ── Conversación 2: Misiones Apolo ─────────────────────────────
            [
                'conversation_id' => 2, 'user_id' => 2,
                'content'  => 'El 20 de julio de 1969, Neil Armstrong fue la primera persona en pisar la Luna 🚀',
                'created_at' => $now->copy()->subMinutes(120), 'updated_at' => $now->copy()->subMinutes(120),
            ],
            [
                'conversation_id' => 2, 'user_id' => 99,
                'content'  => 'El Apolo 11 pasó 21.5 horas en la superficie. Armstrong y Aldrin recogieron 21.5 kg de rocas lunares 🪨',
                'created_at' => $now->copy()->subMinutes(119), 'updated_at' => $now->copy()->subMinutes(119),
            ],
            [
                'conversation_id' => 2, 'user_id' => 3,
                'content'  => 'Fueron 12 astronautas en total. El último fue Gene Cernan en el Apolo 17, diciembre de 1972 👨‍🚀',
                'created_at' => $now->copy()->subMinutes(115), 'updated_at' => $now->copy()->subMinutes(115),
            ],
            [
                'conversation_id' => 2, 'user_id' => 1,
                'content'  => 'El Apolo 13 es el más dramático. Una explosión obligó a abortar el alunizaje 💥',
                'created_at' => $now->copy()->subMinutes(110), 'updated_at' => $now->copy()->subMinutes(110),
            ],
            [
                'conversation_id' => 2, 'user_id' => 99,
                'content'  => 'Los astronautas del Apolo 13 usaron el módulo lunar como bote salvavidas rodeando la Luna. ¡Un milagro de ingeniería! 🙏',
                'created_at' => $now->copy()->subMinutes(109), 'updated_at' => $now->copy()->subMinutes(109),
            ],
            [
                'conversation_id' => 2, 'user_id' => 3,
                'content'  => 'El programa Artemis planea llevar la primera mujer a la Luna 👩‍🚀🌙',
                'created_at' => $now->copy()->subMinutes(100), 'updated_at' => $now->copy()->subMinutes(100),
            ],

            // ── Conversación 3: Astronomía Lunar ───────────────────────────
            [
                'conversation_id' => 3, 'user_id' => 3,
                'content'  => 'La temperatura lunar varía entre -173°C de noche y +127°C de día. ¡300 grados de diferencia! 🌡️',
                'created_at' => $now->copy()->subMinutes(30), 'updated_at' => $now->copy()->subMinutes(30),
            ],
            [
                'conversation_id' => 3, 'user_id' => 99,
                'content'  => 'Sin atmósfera no hay quien amortigüe el calor. Un día lunar dura ~14 días terrestres, por eso los extremos son brutales 🔆❄️',
                'created_at' => $now->copy()->subMinutes(29), 'updated_at' => $now->copy()->subMinutes(29),
            ],
            [
                'conversation_id' => 3, 'user_id' => 1,
                'content'  => 'Sin atmósfera el cielo es negro incluso de día. Y no hay sonido, solo silencio absoluto 🔇',
                'created_at' => $now->copy()->subMinutes(25), 'updated_at' => $now->copy()->subMinutes(25),
            ],
            [
                'conversation_id' => 3, 'user_id' => 2,
                'content'  => 'La gravedad lunar es 1/6 de la terrestre. Con 60 kg en la Tierra, pesas solo 10 kg en la Luna ⚖️',
                'created_at' => $now->copy()->subMinutes(20), 'updated_at' => $now->copy()->subMinutes(20),
            ],
            [
                'conversation_id' => 3, 'user_id' => 3,
                'content'  => 'El cráter Aitken Basin tiene 2,500 km de diámetro y 8 km de profundidad. ¡El más grande de la Luna! 🕳️',
                'created_at' => $now->copy()->subMinutes(15), 'updated_at' => $now->copy()->subMinutes(15),
            ],
            [
                'conversation_id' => 3, 'user_id' => 99,
                'content'  => 'Hay agua en la Luna en forma de hielo en los cráteres polares donde nunca llega el sol. Clave para futuras colonias 💧🌙',
                'created_at' => $now->copy()->subMinutes(14), 'updated_at' => $now->copy()->subMinutes(14),
            ],
        ]);

        $this->command->info('🌙 Conversaciones lunares creadas exitosamente!');
    }
}
