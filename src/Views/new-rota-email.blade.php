<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<body>
<table border="1" cellpadding="10">
    <thead>
        <th>Monday</th>
        <th>Tuesday</th>
        <th>Wednesday</th>
        <th>Thursday</th>
        <th>Friday</th>
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
</body>
</html>