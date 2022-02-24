@foreach($audit as $topLevel => $topLevelData)
<table class="file-audit">
    <tr>
        <td colspan="2"><h3>{{$topLevel}}</h3></td>
    </tr>
    @foreach($topLevelData as $secondLevel => $secondLevelData)
    <tr>
        <td colspan="2"><h4>{{$secondLevel}}</h4></td>
    </tr>
    @foreach($secondLevelData as $thirdLevel => $thirdLevelData)
    <tr>
        <td class="third-level">{{$thirdLevel}}</td>
        @if(empty($thirdLevelData))
        <td>Missing</td>
        @else
        <td><a href="{{$thirdLevelData}}" target="_blank">{{$thirdLevelData}}</a></td>
        @endif
    </tr>
    @endforeach
    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    @endforeach
</table>
@endforeach