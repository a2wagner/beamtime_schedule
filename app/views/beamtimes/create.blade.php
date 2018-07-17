@extends('layouts.default')

@section('title')
Create New Beamtime
@stop

@section('css')
{{ HTML::style('css/datepicker.css') }}
@parent
@stop

@section('styles')
@parent
.datepicker.dropdown-menu {
  top: 0;
  left: 0;
  padding: 4px;
  margin-top: 1px;
}

.fixed-text-input {
  position: absolute;
  display: block;
  right: 70px;
  top: 10px;
  z-index: 3;
}
@stop

@section('scripts')
{{ HTML::script('js/bootstrap-datepicker.js') }}
<script type='text/javascript'>
    //$('.datepicker').datepicker();

    var nowTemp = new Date();
    var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

    var begin = $('#dpd1').datepicker({
      onRender: function(date) {
        return date.valueOf() < now.valueOf() ? 'disabled' : '';
      },
      weekStart: 1  //0 sunday, 1 monday ...
    }).on('changeDate', function(ev) {
      if (ev.date.valueOf() > end.date.valueOf()) {
        var newDate = new Date(ev.date)
        newDate.setDate(newDate.getDate() + 13);
        end.setValue(newDate);
        // set subscription start value
        var subDate = new Date(ev.date)
        subDate.setDate(subDate.getDate() - 30);
        sub_start.setValue(subDate);
      }
      begin.hide();
      $('#dpd2')[0].focus();
    }).data('datepicker');
    var end = $('#dpd2').datepicker({
      onRender: function(date) {
        return date.valueOf() <= begin.date.valueOf() ? 'disabled' : '';
      },
      weekStart: 1
    }).on('changeDate', function(ev) {
      end.hide();
    }).data('datepicker');

    // subscription start
    var sub_start = $('#dpd3').datepicker({
      onRender: function(date) {
        var sub = new Date(now)
        sub.setDate(sub.getDate() - 60);
        return date.valueOf() < sub.valueOf() ? 'disabled' : '';
      },
      weekStart: 1  //0 sunday, 1 monday ...
    }).on('changeDate', function(ev) {
      sub_start.hide();
    }).data('datepicker');


/* Manage hiding or showing subscription start date based on the (non-)ticked checkbox */

$('.sub_check').change(function() {
    $(".sub_start_form").toggle();
});

/* plus / minus buttons for shift duration field */

// disable buttons if min/max value is start value
$(document).ready(function () {
    // first hide subscription start if unchecked after loading the page
    if (!$('.sub_check').checked)
        $('.sub_start_form').hide();

    // now manage the buttons
    var input = $('.input-number');
    minValue = parseInt(input.attr('min'));
    maxValue = parseInt(input.attr('max'));
    valueCurrent = parseInt(input.val());
    name = input.attr('name');

    if (valueCurrent < minValue || valueCurrent > maxValue) {
        window.alert('Wrong start value given for shift duration!');
        input.val(minValue);
    } else if (valueCurrent == minValue) 
        $(".btn-number[data-type='minus'][data-field='"+name+"']").attr('disabled', true);
    else if (valueCurrent == maxValue)
        $(".btn-number[data-type='plus'][data-field='"+name+"']").attr('disabled', true);
});

// function of +/- buttons, disabling them when min/max value reached as well as prevent non-integer input
$('.btn-number').click(function(e){
    e.preventDefault();

    fieldName = $(this).attr('data-field');
    type      = $(this).attr('data-type');
    var input = $("input[name='"+fieldName+"']");
    var currentVal = parseInt(input.val());

    if (!isNaN(currentVal)) {
        if (type == 'minus') {
            if (currentVal > input.attr('min')) {
                input.val(currentVal - 1).change();
            }
        } else if (type == 'plus') {
            if (currentVal < input.attr('max')) {
                input.val(currentVal + 1).change();
            }
        }
    } else {
        input.val(0);
    }
});
$('.input-number').focusin(function(){
    $(this).data('oldValue', $(this).val());
});
$('.input-number').change(function() {
    minValue = parseInt($(this).attr('min'));
    maxValue = parseInt($(this).attr('max'));
    valueCurrent = parseInt($(this).val());

    name = $(this).attr('name');

    if (valueCurrent < minValue || valueCurrent > maxValue) {
        alert('Sorry, the value is out of range');
        $(this).val($(this).data('oldValue'));
    } else if (valueCurrent == minValue) {
        $(".btn-number[data-type='minus'][data-field='"+name+"']").attr('disabled', true);
        $(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled');
    } else if (valueCurrent == maxValue) {
        $(".btn-number[data-type='plus'][data-field='"+name+"']").attr('disabled', true);
        $(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled');
    } else if (valueCurrent > minValue && valueCurrent < maxValue) {
        $(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled');
        $(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled');
    } else {
        alert('Sorry, the value is invalid');
        $(this).val($(this).data('oldValue'));
    }
});
$(".input-number").keydown(function (e) {
    // Allow: backspace, delete, tab, escape, enter and .
    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
        // Allow: Ctrl+A
        (e.keyCode == 65 && e.ctrlKey === true) || 
        // Allow: home, end, left, right
        (e.keyCode >= 35 && e.keyCode <= 39)) {
        // let it happen, don't do anything
            return;
        }
    // Ensure that it is a number and stop the keypress
    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
    }
});
</script>
@stop

@section('content')
<div class="col-lg-10 col-lg-offset-1">
    <div class="page-header">
        <h2>Create a new beamtime</h2>
    </div>

    {{ Form::open(['route' => 'beamtimes.store', 'class' => 'form-horizontal', 'role' => 'form']) }}
        <fieldset>
            <div class="form-group {{{ $errors->has('name') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('name', 'Name: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-8">
                    {{ Form::text('name', Input::old('name'), array('class' => 'form-control', 'id' => 'inputError2', 'autofocus' => 'autofocus')) }}
                    {{ $errors->has('name') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('name') }}</p>
                </div>
            </div>
            <div class="form-group {{{ $errors->has('description') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('description', 'Short description: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-8">
                    {{ Form::textarea('description', Input::old('description'), array('class' => 'form-control', 'rows' => '3', 'placeholder' => 'optional', 'id' => 'inputError2', 'autofocus' => 'autofocus')) }}
                    {{ $errors->has('description') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('description') }}</p>
                </div>
            </div>

            <div class="form-group {{{ $errors->has('start') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('start', 'Start&nbsp;date: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-5">
                    {{ Form::text('start', Input::old('start'), array('class' => 'form-control datepicker', 'id' => 'dpd1', 'data-date-format' => 'yyyy-mm-dd')) }}
                    {{ $errors->has('start') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('start') }}</p>
                </div>
                <div class="col-lg-3">
                	@if (Input::old('sTime'))
                    {{ Form::select('sTime', $hours, Input::old('sTime'), array('class' => 'form-control', 'id' => 'inputError2')) }}
                    @else
                    {{ Form::select('sTime', $hours, '8', array('class' => 'form-control')) }}
                    @endif
                </div>
            </div>
            <div class="form-group {{{ $errors->has('end') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('end', 'End&nbsp;date: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-5">
                    {{ Form::text('end', Input::old('end'), array('class' => 'form-control datepicker', 'id' => 'dpd2', 'data-date-format' => 'yyyy-mm-dd')) }}
                    {{ $errors->has('end') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('end') }}</p>
                </div>
                <div class="col-lg-3">
                	@if (Input::old('eTime'))
                    {{ Form::select('eTime', $hours, Input::old('eTime'), array('class' => 'form-control', 'id' => 'inputError2')) }}
                    @else
                    {{ Form::select('eTime', $hours, '6', array('class' => 'form-control')) }}
                    @endif
                </div>
            </div>

            <div class="form-group {{{ $errors->has('duration') ? 'has-error has-feedback' : '' }}}">
                {{ Form::label('duration', 'Shift duration: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-5">
                    <div class="input-group">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default btn-number" data-type="minus" data-field="duration">
                                <span class="glyphicon glyphicon-minus"></span>
                            </button>
                        </span>
                        @if (Input::old('duration'))
                        {{ Form::text('duration', Input::old('duration'), array('class' => 'form-control input-number', 'id' => 'inputError2', 'min' => '1', 'max' => '10')) }}
                        {{ $errors->has('duration') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                        <p class="help-block">{{ $errors->first('duration') }}</p>
                        @else
                        {{ Form::text('duration', 8, array('class' => 'form-control input-number', 'min' => '1', 'max' => '10')) }}
                        @endif
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default btn-number" data-type="plus" data-field="duration">
                                <span class="glyphicon glyphicon-plus"></span>
                            </button>
                        </span>
                        <span class="fixed-text-input">
                          hours
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                {{ Form::label('set_sub', 'Shift Registration: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-8">
                    <div class="checkbox sub_check">
                        <label for="set_sub">
                        {{ Form::checkbox('set_sub', 1, Input::old('set_sub') ? Input::old('set_sub') : false) }}
                        Set subscription start for this beamtime and enforce subscription rules
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group {{{ $errors->has('sub_start') ? 'has-error has-feedback' : '' }}} sub_start_form" style="display: none;">
                {{ Form::label('sub_start', 'Subscription Start: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-5">
                    {{ Form::text('sub_start', Input::old('sub_start'), array('class' => 'form-control datepicker', 'id' => 'dpd3', 'data-date-format' => 'yyyy-mm-dd')) }}
                    {{ $errors->has('sub_start') ? '<span class="glyphicon glyphicon-remove form-control-feedback"></span>' : '' }}
                    <p class="help-block">{{ $errors->first('sub_start') }}</p>
                </div>
                <div class="col-lg-3">
                    @if (Input::old('subTime'))
                    {{ Form::select('subTime', $hours, Input::old('subTime'), array('class' => 'form-control', 'id' => 'inputError2')) }}
                    @else
                    {{ Form::select('subTime', $hours, '9', array('class' => 'form-control')) }}
                    @endif
                </div>
            </div>

            <div class="form-group">
                {{ Form::label('enforce_rc', 'Enforce RC: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-8">
                    <div class="checkbox">
                        <label>
                        {{ Form::checkbox('enforce_rc', 1, Input::old('enforce_rc') ? Input::old('enforce_rc') : true) }}
                        Enforce Run Coordinator subscription
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                {{ Form::label('experience_block', 'Experience Block: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-8">
                    <div class="checkbox">
                        <label>
                        {{ Form::checkbox('experience_block', 1, Input::old('experience_block') ? Input::old('experience_block') : true) }}
                        Block subscription to auto-extended shifts for users with less than {{{ Shift::EXPERIENCE_BLOCK }}} taken shifts
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                {{ Form::label('weekday_crew1', 'Weekday shifts: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-8">
                    <div class="checkbox">
                        <label>
                        {{ Form::checkbox('weekday_crew1', 1, Input::old('weekday_crew1') ? Input::old('weekday_crew1') : false) }}
                        Set shift workers to 1 for weekday shifts
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                {{ Form::label('day_late_crew1', 'Day & late shifts: ', array('class' => 'col-lg-2 control-label')) }}
                <div class="col-lg-8">
                    <div class="checkbox">
                        <label>
                        {{ Form::checkbox('day_late_crew1', 1, Input::old('day_late_crew1') ? Input::old('day_late_crew1') : true) }}
                        Set shift workers to 1 for all day and late shifts
                        </label>
                    </div>
                </div>
            </div>


            <div class="form-group">
                <div class="col-lg-10 col-lg-offset-2">
                    {{ Form::submit('Create Beamtime', array('class' => 'btn btn-primary')) }}
                </div>
            </div>
        </fieldset>
    {{ Form::close() }}
</div>
@stop
