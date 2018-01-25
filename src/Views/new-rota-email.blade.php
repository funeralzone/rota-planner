<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<body>
<p>Dear all,</p>
<p>Below is next week's rota.</p>
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
<p>Instructions: When you are on duty, you are responsible for the following:</p>
<ol>
    <li>Empty the dishwasher in the morning (start it if it failed to start the night before).</li>
    <li><b>Make sure the bins are emptied</b> if they are full (or approaching capacity). This should be checked throughout the day.</li>
    <li>Start the dishwasher before leaving the office at the end of the day.</li>
</ol>
<p>Everyone is responsible for loading their own dishes into the dishwasher throughout the day.</p>
<p>Regards,<br>Dishwasher bot</p>
</body>
</html>
