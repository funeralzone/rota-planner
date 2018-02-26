<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<body>
<p>Dear all,</p>
<p>Here are the dishwasher assignees for next week.</p>
<table border="1" cellpadding="10">
    <thead>
        @foreach ($rota->getAssignedTimeSlots() as $timeSlot)
            <th>{{ $timeSlot->getTimeSlot()->getName() }}</th>
        @endforeach
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
<p>Responsibilities when on duty:</p>
<ol>
    <li>Empty the dishwasher in the morning (start it if it failed to start the night before).</li>
    <li>Make sure the bins are emptied if they are full (or approaching capacity). This should be checked throughout the day.</li>
    <li>Start the dishwasher before leaving the office at the end of the day.</li>
</ol>
<p>Everyone is responsible for loading their own dishes into the dishwasher throughout the day. Please don't leave dishes in the sink or on the side.</p>
<p>Regards,<br>Dishwasher bot</p>
</body>
</html>
