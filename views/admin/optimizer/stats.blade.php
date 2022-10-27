@php
$totalFiles = 0;
$totalBytes = 0;
$optimizedBytes = 0;
$savedBytes = 0;
$savings = 0;

if (!empty($globalStats)) {
	$totalFiles = $globalStats['totalOptimized'];
	$totalBytes = $globalStats['totalBytes'];
	$optimizedBytes = $globalStats['optimizedBytes'];
	$savedBytes = $globalStats['savedBytes'];
	$savings = ($totalBytes == 0) ? 0 : ($savedBytes / $totalBytes);
}

/** @var \MediaCloud\Plugin\Tools\Optimizer\OptimizerAccountStatus $accountStatus */
if (!empty($accountStatus)) {
    $progress = ($accountStatus->quota() == 0) ? 0 : min(1.0, $accountStatus->used() / $accountStatus->quota());

    $pcolor = "#7ED321";
    if (($progress > 0.33) && ($progress < 0.66)) {
        $pcolor = "#F5A623";
    } else if ($progress >= 0.66) {
        $pcolor = "#D0021B";
    }

	if ($accountStatus->quotaType() === \MediaCloud\Plugin\Tools\Optimizer\OptimizerConsts::QUOTA_API_CALLS) {
		if ($progress < 1.0) {
            $quotaLabel = sprintf('%.0f', $progress * 100).'%';
		} else {
			$quotaLabel = 'Over Quota';
		}
	} else {
		$remaining = max(0, $accountStatus->quota() - $accountStatus->used());
		if ($remaining > 0) {
            $quotaLabel = size_format($remaining)."<br/>Remaining";
		} else {
			$quotaLabel = 'Over Quota';
		}
	}
}
@endphp

<div class="optimize-stats-container">
    <div class="optimize-stats">
        <div class="optimize-stats-cell">
            <h4>Savings</h4>
            <div class="graph">
                @include('base.ui.circle-graph', ['progress' => $savings, 'circleWidth' => 65])
                <div class="label">{{sprintf('%.0f', $savings * 100)}}%</div>
            </div>
        </div>
        @if(!empty($accountStatus))
            <div class="optimize-stats-cell">
                <h4>Account Quota</h4>
                <div class="graph">
                    @include('base.ui.circle-graph', ['progress' => $progress, 'circleWidth' => 65, 'progressColor' => $pcolor])
                    <div class="label">{!! $quotaLabel !!}</div>
                </div>
            </div>
        @endif
    </div>
    <div class="optimize-overall-stats">
        Overall, you have optimized <strong>{{size_format($totalBytes,2)}}</strong> of images ({{$totalFiles}} files) saving <strong>{{size_format($savedBytes, 2)}} ({{sprintf('%.0f', $savings * 100)}}%)</strong> total file size.
    </div>
</div>
