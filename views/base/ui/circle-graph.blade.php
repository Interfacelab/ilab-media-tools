<?php
$circleWidth = isset($circleWidth) ? $circleWidth : 50;
$borderWidth = isset($borderWidth) ? $borderWidth : 15;
$progressColor = isset($progressColor) ? $progressColor : '#50ADE2';
$circleRadius = ($circleWidth - ($borderWidth / 2));
$c = 2 * M_PI * $circleRadius;
$p = $c * (1 - $progress);
?>

<svg style="transform:rotate(-90deg)" width="{{$circleWidth * 2}}" height="{{$circleWidth * 2}}" viewBox="0 0 {{$circleWidth * 2}} {{$circleWidth * 2}}" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <circle id="Oval" fill="none" stroke="#DDD" stroke-width="{{$borderWidth}}" cx="{{$circleWidth}}" cy="{{$circleWidth}}" r="{{$circleRadius}}"></circle>
    <circle id="Oval" fill="none" stroke="{{$progressColor}}" stroke-width="{{$borderWidth}}" cx="{{$circleWidth}}" cy="{{$circleWidth}}" r="{{$circleRadius}}" stroke-dasharray="{{$c}}" stroke-dashoffset="{{$p}}"></circle>
</svg>
