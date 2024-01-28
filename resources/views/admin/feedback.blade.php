@extends('admin.layouts.app')

@section('header_title')
    Feedback
@endsection

@section('page_title')
    <h2 class="small display_table_cell_md">Feedback</h2>
@endsection

@section('content')

@if (count($feedbacks) > 0)
<div class="row">
    <div class="col-sm-12 text-center" data-animation="scaleAppear">
        <table>
            <tr>
                <th>Message</th>
                <th>Date Submitted</th>
                <th>Action</th>
            </tr>
            @foreach ($feedbacks as $feedback)
                <tr>
                    <td>{{ $feedback->message }}</td>
                    <td>{{ date('Y-m-d H:i:s', strtotime($feedback->created_at)) }}</td>
                    <td>
                        @if ($feedback->resolved != 0)
                            Resolved
                        @else
                            <a href="{{ $feedback->getUrl() }}" >
                                <button name="submit_feedback" class="theme_button color4 min_width_button margin_0">Go to {{ $feedback->entity_type }}</button>
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
@endif

@endsection