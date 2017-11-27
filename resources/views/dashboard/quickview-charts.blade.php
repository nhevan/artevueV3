<form method="GET" action="/dashboard" class="navbar-form" style="text-align: center;">
    {{ csrf_field() }}
    <div class="form-group">
        <input name='start_date' type="date" class="form-control input-sm" placeholder="Start Date" value="{{ \Carbon\Carbon::parse($dataset['start_date'])->format("Y-m-d")}}">
        <input name='end_date' type="date" class="form-control input-sm" placeholder="End Date" value="{{ \Carbon\Carbon::parse($dataset['end_date'])->format("Y-m-d") }}">

        <select class="form-control input-sm" name='interval'>
          <option>Select Interval</option>
          <option value="hour" {{ $dataset['interval'] == 'hour' ? "selected":"" }} >Hour</option>
          <option value="day" {{ $dataset['interval'] == 'day' ? "selected":"" }} >Day</option>
          <option value="month" {{ $dataset['interval'] == 'month' ? "selected":"" }} >Month</option>
        </select>
    </div>
    <button type="submit" class="btn btn-info btn-sm">Show</button>
    - or -
    <a href="/dashboard" class="btn btn-success btn-sm">See Today</a>
</form>    

@include('dashboard.quickview-line-chart')

@include('dashboard.quickview-bar-chart')