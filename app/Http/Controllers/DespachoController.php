<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Cupo;
use App\Models\Despacho;
use App\Rules\PlacaValida;
use Illuminate\Http\Request;

class DespachoController extends Controller
{
    public function consultarCupo(Request $request)
    {
        $request->validate(['placa' => ['required', 'string', new PlacaValida]]);

        $placa = Vehiculo::normalizarPlaca($request->placa);
        $vehiculo = Vehiculo::where('placa', $placa)->first();

        if (!$vehiculo) {
            return response()->json(['estado' => 'no_encontrado', 'mensaje' => 'Vehículo no registrado'], 404);
        }

        $periodoActual = now()->format('Y-m');
        $cupo = Cupo::where('vehiculo_id', $vehiculo->id)->where('periodo', $periodoActual)->first();

        if (!$cupo) {
            return response()->json(['estado' => 'sin_cupo', 'mensaje' => 'No tiene cupo asignado este mes'], 404);
        }

        $disponible = $cupo->litros_asignados - $cupo->litros_consumidos;

        return response()->json([
            'estado' => 'ok',
            'placa' => $vehiculo->placa,
            'tipo_uso' => $vehiculo->tipo_uso,
            'tipo_combustible' => $vehiculo->tipo_combustible,
            'marca' => $vehiculo->marca,
            'modelo' => $vehiculo->modelo,
            'foto_url' => $vehiculo->foto_url,
            'litros_asignados' => $cupo->litros_asignados,
            'litros_consumidos' => $cupo->litros_consumidos,
            'litros_disponibles' => $disponible,
        ]);
    }

    public function registrarDespacho(Request $request)
    {
        $request->validate([
            'placa' => ['required', 'string', new PlacaValida],
            'estacion_id' => 'required|exists:estaciones,id',
            'litros' => 'required|numeric|min:0.1',
        ]);

        $placa = Vehiculo::normalizarPlaca($request->placa);
        $vehiculo = Vehiculo::where('placa', $placa)->first();
        if (!$vehiculo) {
            return response()->json(['estado' => 'no_encontrado'], 404);
        }

        $periodoActual = now()->format('Y-m');
        $cupo = Cupo::where('vehiculo_id', $vehiculo->id)->where('periodo', $periodoActual)->first();
        if (!$cupo) {
            return response()->json(['estado' => 'sin_cupo'], 404);
        }

        $disponible = $cupo->litros_asignados - $cupo->litros_consumidos;

        if ($request->litros > $disponible) {
            return response()->json(['estado' => 'cupo_excedido', 'litros_disponibles' => $disponible], 422);
        }

        Despacho::create([
            'vehiculo_id' => $vehiculo->id,
            'estacion_id' => $request->estacion_id,
            'litros_despachados' => $request->litros,
            'fecha_hora' => now(),
        ]);

        $cupo->litros_consumidos += $request->litros;
        $cupo->save();

        return response()->json(['estado' => 'registrado', 'litros_disponibles' => $disponible - $request->litros]);
    }
}