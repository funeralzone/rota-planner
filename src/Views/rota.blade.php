@extends('layouts/master')

@section('content')

    <h1>{{ $pageTitle }}</h1>

    <table>
        <thead>
            <tr>
                <th>Monday</th>
                <th>Tuesday</th>
                <th>Wednesday</th>
                <th>Thursday</th>
                <th>Friday</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach ($rota->getAssignedTimeSlots() as $timeSlot)
                    <td>
                    @foreach ($timeSlot->getAssignees() as $assignee)
                        {{ $assignee->getName() }} <br>
                    @endforeach
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>

@endsection