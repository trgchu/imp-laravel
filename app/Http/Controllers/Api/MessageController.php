<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    // ── Palabras clave → respuestas del bot Selene ────────────────────────
    private array $lunarResponses = [
        'fases' => [
            'La Luna tiene 8 fases: nueva, creciente iluminada, cuarto creciente, gibosa creciente, llena, gibosa menguante, cuarto menguante y creciente menguante 🌑🌒🌓🌔🌕🌖🌗🌘',
            'Un ciclo completo de fases lunares dura 29.5 días (mes sinódico). ¡Así nacieron los primeros calendarios humanos! 📅',
        ],
        'luna llena' => [
            'La Luna llena ocurre cuando la Tierra está entre el Sol y la Luna. Vemos el 100% de su cara iluminada 🌕✨',
        ],
        'superluna' => [
            'Una Superluna ocurre cuando la luna llena coincide con el perigeo (punto más cercano a la Tierra). ¡Se ve hasta 14% más grande y 30% más brillante! 🌕',
        ],
        'apolo' => [
            'El programa Apolo llevó a 12 personas a la Luna entre 1969 y 1972. Neil Armstrong fue el primero el 20 de julio de 1969 🚀',
            'El Apolo 13 sufrió una explosión en el tanque de oxígeno. Los astronautas usaron el módulo lunar como bote salvavidas y volvieron a salvo 💥🙏',
            'Los astronautas del Apolo trajeron 382 kg de rocas lunares a la Tierra. ¡Siguen siendo estudiadas hoy! 🪨',
        ],
        'artemis' => [
            'El programa Artemis de la NASA planea llevar la primera mujer y la primera persona de color a la Luna 👩‍🚀🌙',
            'Artemis busca establecer una base lunar permanente como trampolín hacia Marte 🚀🪐',
        ],
        'gravedad' => [
            'La gravedad lunar es 1/6 de la terrestre (1.62 m/s²). ¡En la Luna podrías saltar hasta 3 metros de altura! ⚖️🦘',
        ],
        'temperatura' => [
            'La superficie lunar alcanza +127°C al mediodía lunar y baja a -173°C en la noche. Sin atmósfera no hay quien amortigüe el calor 🌡️',
        ],
        'agua' => [
            'En 2009 la NASA confirmó agua en forma de hielo en los cráteres polares, donde nunca llega la luz solar. Clave para futuras misiones 💧❄️',
        ],
        'cráter' => [
            'La Luna tiene más de 5,000 cráteres con más de 20 km de diámetro. El mayor es la Cuenca Aitken: 2,500 km de ancho y 8 km de profundidad 🕳️',
        ],
        'crater' => [
            'Los cráteres se formaron por impactos de meteoritos hace miles de millones de años. Sin atmósfera, no hay erosión que los borre ☄️',
        ],
        'marea' => [
            'Las mareas oceánicas son causadas por la atracción gravitacional de la Luna. En luna nueva y llena son mareas vivas; en cuartos, mareas muertas 🌊🌙',
        ],
        'distancia' => [
            'La Luna está a 384,400 km de media. La luz tarda 1.28 segundos en recorrer esa distancia ✨',
            'La distancia varía: en el perigeo son ~356,500 km y en el apogeo ~406,700 km 🌍🌙',
        ],
        'origen' => [
            'La teoría más aceptada es el Gran Impacto: hace 4,500 millones de años, el protoplaneta Theia chocó con la Tierra joven y los restos formaron la Luna 💥🌙',
        ],
        'atmosfera' => [
            'La Luna no tiene atmósfera, por eso el cielo es negro incluso de día. Sin atmósfera no hay sonido ni quien proteja del calor o los meteoritos 🔇',
        ],
        'atmósfera' => [
            'Sin atmósfera, la Luna es bombardeada directamente por la radiación solar y los meteoritos. Un día lunar equivale a ~14 días terrestres 🌑',
        ],
        'rotacion' => [
            'La Luna tarda lo mismo en girar sobre sí misma que en orbitar la Tierra. Por eso siempre vemos la misma cara: se llama rotación sincrónica 🌒',
        ],
        'rotación' => [
            'Nunca hemos visto la cara oculta de la Luna a simple vista. La primera foto de ese lado la tomó la sonda soviética Luna 3 en 1959 🛸',
        ],
    ];

    private array $genericResponses = [
        '¡Interesante! Puedo contarte sobre las fases lunares, cráteres, misiones Apolo, mareas, temperatura o el origen de la Luna 🌙',
        'Como guardiana lunar, estoy aquí para responder todo sobre nuestro satélite. ¿Qué quieres saber? 🌕✨',
        'Pregúntame sobre: fases, gravedad, distancia, agua en la Luna, misiones espaciales o el programa Artemis 🚀🌙',
        'La Luna lleva 4,500 millones de años acompañando a la Tierra. ¿Qué aspecto lunar te intriga más? 🔭',
    ];

    // ─────────────────────────────────────────────────────────────────────
public function index(Request $request, Conversation $conversation)
{
    return $conversation->messages()
        ->with('user', 'conversation')
        ->orderBy('created_at', 'asc')
        ->paginate(50);
}
    // ─────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content'         => 'required|string|max:1000',
            'conversation_id' => 'required|exists:conversations,id',
        ]);

        $conversation = Conversation::findOrFail($validated['conversation_id']);

        if (!auth()->user()->conversations->contains($conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Guardar mensaje del usuario
        $message = auth()->user()->messages()->create($validated);
        $message->load('user', 'conversation');

        MessageSent::dispatch($message);

        // Respuesta automática del bot Selene 🌙
        $this->dispatchBotResponse($conversation, $validated['content']);

        return response()->json($message, 201);
    }

    // ─────────────────────────────────────────────────────────────────────
    private function dispatchBotResponse(Conversation $conversation, string $userContent): void
    {
        $selene = User::firstOrCreate(
            ['email' => 'selene@luna.space'],
            [
                'name'     => 'Selene 🌙',
                'password' => bcrypt(Str::random(32)),
            ]
        );

        // Asegurarse de que Selene está en la conversación
        if (!$conversation->users->contains($selene->id)) {
            $conversation->users()->attach($selene->id);
        }

        $responseText = $this->generateResponse($userContent);

        $botMessage = Message::create([
            'conversation_id' => $conversation->id,
            'user_id'         => $selene->id,
            'content'         => $responseText,
        ]);

        $botMessage->load('user', 'conversation');
        MessageSent::dispatch($botMessage);
    }

    // ─────────────────────────────────────────────────────────────────────
    private function generateResponse(string $text): string
    {
        $lower = mb_strtolower($text, 'UTF-8');

        foreach ($this->lunarResponses as $keyword => $responses) {
            if (str_contains($lower, $keyword)) {
                return $responses[array_rand($responses)];
            }
        }

        return $this->genericResponses[array_rand($this->genericResponses)];
    }

    // ─────────────────────────────────────────────────────────────────────
    public function show(Message $message)
    {
        return $message->load('user', 'conversation');
    }

    public function update(Request $request, Message $message)
    {
        $this->authorize('update', $message);

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message->update($validated);

        return response()->json($message);
    }

    public function destroy(Message $message)
    {
        $this->authorize('delete', $message);
        $message->delete();
        return response()->json(null, 204);
    }
}
