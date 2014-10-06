@extends('layouts.default')

@section('title')
Beamtimes
@stop

@section('scripts')
{{ HTML::script('js/laravel.js') }}
@stop

@section('content')
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
        <h2>All Beamtimes</h2>
    </div>

Dropdown Menü mit allen Strahlzeiten, paar Infos wie Start, Dauer, Anzahl Schichten, ... 
Zeige aktuelle oder nächste beginnende Strahlzeit, Infos Ermittlung: 
http://stackoverflow.com/questions/13026334/get-closest-date-from-mysql-table
http://stackoverflow.com/questions/6186962/sql-query-to-show-nearest-date
http://www.dintillion.com/?p=135
http://www.justskins.com/forums/finding-a-date-closest-40535.html#post121244
--> Logik, ob Strahlzeit ist oder nicht. Vlt. finde nächsten Start, wo DATEDIFF negativ und am kleinsten, dann teste, ob Ende größer now --> aktuell Strahlzeit, sonst DATEDIFF positiv am kleinsten ist beginnende Strahlzeit; zeige jeweils aktuelle oder folgende an

    @if ($beamtimes->count())
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Beamtime Name</th>
          <th>Start</th>
          <th>#Shifts</th>
          <th>Status</th>
          @if (Auth::user()->isAdmin)
          <th class="text-center">Actions</th>
          @endif
        </tr>
      </thead>
      <tbody>
        @foreach ($beamtimes as $beamtime)
        <tr>
          {{-- Check if the beamtime contain shifts to avoid errors --}}
          @if (is_null($beamtime->shifts->first()))
          @if (Auth::user()->isAdmin)
          <td colspan="4"><h4 class="text-danger">Beamtime contains no shifts!</h4></td>
          <td class="text-center"><a href="/beamtimes/{{{$beamtime->id}}}" data-method="delete" data-confirm="Are you sure to delete this beamtime?" class="btn btn-danger btn-sm"><span class="fa fa-times"></span>Delete</a></td>
          @endif
          @else
          <td>{{ link_to("/beamtimes/{$beamtime->id}", $beamtime->name) }}</td>
          <td>{{ $beamtime->shifts->first()->start }}</td>
          <td>{{ $beamtime->shifts()->count() }}</td>
          <?php  // calculate some time information of the beamtime
          	$now = new DateTime();
          	$start = new DateTime($beamtime->shifts->first()->start);
          	$end = new DateTime($beamtime->shifts->last()->start);
          	$dur = 'PT' . $beamtime->shifts->last()->duration . 'H';
          	$end->add(new DateInterval($dur));
          ?>
          @if ($now < $start)
          <?php $diff = $now->diff($start); ?>
          <td class="text-primary">Starting in {{{ $diff->format('%a days and %h hours') }}}</td>
          @elseif ($now > $end)
          <?php $diff = $now->diff($end); ?>
          <td class="text-muted">Ended {{{ $diff->format('%a days ago') }}}</td>
          @else
          <?php  // calculate progress of the current beamtime
          	$diff = $now->diff($start);
          ?>
          <td class="text-success">Running for {{{ $diff->format('%a days and %h hours') }}}</td>
          @endif
          @if (Auth::user()->isAdmin)
          <td class="text-center">
            <a class='btn btn-primary btn-xs' href="/beamtimes/{{{$beamtime->id}}}/edit"><span class="fa fa-pencil"></span> Edit</a> 
            <a href="/beamtimes/{{{$beamtime->id}}}" data-method="delete" data-confirm="Are you sure to delete this beamtime?" class="btn btn-danger btn-xs"><span class="fa fa-times"></span> Del</a>
          </td>
          @endif
          @endif  {{-- end of check if beamtime contains shifts --}}
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    {{ $beamtimes->links() }}
    @else
    <h3 class="text-danger">No beamtimes found</h3>
    @endif
</div>
@stop

