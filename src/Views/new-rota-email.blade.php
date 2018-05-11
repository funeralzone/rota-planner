<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<body>
<p>Dear all,</p>
<p>Here is the Funeral Zone support rota for next week.</p>
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
    <li>Monitor the "Support" column on the <a href="https://trello.com/b/d7g9chuT/fz-current-work">Trello board</a>.</li>
    <li>Report estimated timescales on each ticket you pick up</li>
    <li>Keep communication open with the support team and be sure to inform them of updates</li>
    <li>Chase PR approval for resolved issues</li>
</ol>
</body>
</html>
