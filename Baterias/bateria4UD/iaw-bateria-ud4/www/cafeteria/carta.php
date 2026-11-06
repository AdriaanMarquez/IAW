<?php
// Batería UD4 · bloque 2 · La carta. Apartado 4 de la UD4.
// Pruébala en https://localhost:8447/carta.php (cuando el bloque 1 funcione).
declare(strict_types=1);
$titulo = 'Carta · Cafetería';
$platos = require __DIR__ . '/inc/platos.php';  // la lista de platos: míralo en inc/platos.php
require __DIR__ . '/inc/cabecera.php';

// ✍️2.2 · Una función euros() que reciba un número (float) y devuelva el precio en texto,
//       con dos decimales, coma decimal y el símbolo del euro: 3.2 → «3,20 €». Pista: number_format.
function euros(float $n): string {
    return number_format($n, 2, ',', '.') . ' €';
}

?>
<h2>La carta</h2>
<ul id="carta">
<?php foreach ($platos as $p): ?>
    <li><?= $p['nombre'] ?> — <?= euros($p['precio']) ?></li>
<?php endforeach; ?>
</ul>

<p id="total">Total de la carta: <?= euros(array_sum(array_column($platos, 'precio'))) ?></p>

<h2>Para vegetarianos</h2>
<ul id="vegetarianos">
<?php 
$vegetarianos = array_filter($platos, fn($p) => $p['vegetariano']);
foreach ($vegetarianos as $p): 
?>
    <li><?= $p['nombre'] ?> — <?= euros($p['precio']) ?></li>
<?php endforeach; ?>
</ul>
</body></html>
