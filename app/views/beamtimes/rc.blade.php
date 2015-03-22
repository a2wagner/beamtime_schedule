@extends('layouts.default')

@section('title')
Run Coordinators :: {{ $beamtime->name }}
@stop

@section('scripts')
<script type="text/javascript">
$(document).ready(function() {
    $("body").tooltip({ selector: '[data-toggle=tooltip]' });

});

$("[type='checkbox']").on("click", function() {
  var buttons = $("[rel='tooltip']");
  var checks = $("[type='checkbox']");
  var idx = checks.index($(this));  // index of the clicked checkbox element

  toggleButton(buttons.eq(idx));
});

$(document).ready(function() {
  var buttons = $("[rel='tooltip']");
  var checks = $("[type='checkbox']");

  checks.each(function(i, val) {
    if (val.checked)
      toggleButton(buttons.eq(i));
  });
});

function toggleButton(btn)
{
  if (btn.children("span").first().hasClass("fa-check")) {
    btn.children("span").first().removeClass("fa-check");
    btn.children("span").first().addClass("fa-times");
    btn.attr("data-original-title", "Unsubscribe");
  } else {
    btn.children("span").first().removeClass("fa-times");
    btn.children("span").first().addClass("fa-check");
    btn.attr("data-original-title", "Subscribe");
  }
}
</script>
@stop

@section('content')
{{ Form::open(['route' => array('beamtimes.rc_update', $beamtime->id), 'method' => 'PATCH']) }}
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
      <table width="100%">
        <tr>
          <td>
            <h2>Beamtime: {{{ $beamtime->name }}}</h2>
          </td>
          <td align="right">
            {{ Form::submit('Apply Changes', array('class' => 'btn btn-primary')) }}
            {{ link_to(URL::previous(), 'Cancel', ['class' => 'btn btn-default']) }}
          </td>
        </tr>
      </table>
    </div>
    @if (!empty($beamtime->description))
    <h4>Short beamtime description:</h4>
    <p style="white-space: pre-wrap;">{{ $beamtime->description }}</p>
    @endif

    {{-- Check if the beamtime contain shifts to avoid errors --}}
    @if (is_null($beamtime->shifts->first()))
    <h3 class="text-danger">Beamtime contains no shifts!</h3>
    @else
    @if (isset($beamtime))
    <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>#Shift</th>
          <th>Start</th>
          <th>Duration</th>
          <th>Type</th>
          <th>Run Coordinator</th>
          <th>Subscribed</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 0; $day = ""; $now = new DateTime(); ?>
        @foreach ($rc_shifts as $shift)
        @if ($day != date("l, d.m.Y", strtotime($shift->start)))
        <?php $day = date("l, d.m.Y", strtotime($shift->start)); ?>
        <tr class="active" style="padding-left: 20px;">
          <th colspan=8>{{ $day }}</th>
        </tr>
        @endif
        <tr>
          <td>{{ ++$i }}</td>
          <td>{{ $shift->start }}</td>
          <?php  // calculate actual duration depending on local timezone
          	$start = new DateTime($shift->start);
          	$end = clone($start);
          	$dur = 'PT' . $shift->duration . 'H';
          	$end->add(new DateInterval($dur));
          ?>
          <td>{{ $start->diff($end)->h }} hours</td>
          <td>{{ $shift->type() }}</td>
          {{-- check if a user subscribed to this shift --}}
          @if (!$shift->user->count())
          {{-- if not, then display this --}}
          <td>open</td>
          @else
          {{-- otherwise show the subscribed user --}}
          <td>{{ $shift->user->first()->get_full_name() }}</td>
          @endif
          <td>
            {{-- only show (un-)subscription box if the run coordinator shift is empty or the current user is assigned to it --}}
            @if (!$shift->user->count() || $shift->user->first()->id == Auth::id())
            <div class="checkbox">
              <label>
                {{ Form::checkbox('subscription[]', $shift->id, $shift->user->count() ? $shift->user->first()->id == Auth::id() : false, $now > new DateTime($shift->start) ? array('disabled') : '') }}
                {{-- if a shift start is in the past and the current user is subscribed to it, add a hidden input field to prevent the user from getting unsubscribed due to the disabled checkbox --}}
                @if ($now > new DateTime($shift->start) && $shift->user->count() && $shift->user->first()->id == Auth::id())
                {{ Form::hidden('subscription[]', $shift->id) }}
                @endif
                <a rel="tooltip" data-toggle="tooltip" data-placement="top" data-original-title="Subscribe" class="btn btn-default btn-xs {{{ $now > new DateTime($shift->start) ? 'disabled' : '' }}}"><span class="fa fa-check"></span></a>
              </label>
            </div>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    <div>
      <table border=0 width=95%>
        <tr>
          <td>Total {{ $rc_shifts->count() }} run coordinator shifts</td>
          <td align="right">
            {{ Form::submit('Apply Changes', array('class' => 'btn btn-primary')) }}
          </td>
        </tr>
      </table>
    </div>
    @else
    <h3 class="text-danger">Beamtime not found!</h3>
    @endif
    @endif  {{-- end of check if beamtime contains shifts --}}
</div>
{{ Form::close() }}
@stop

