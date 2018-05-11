<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<body>
<p>Hi {{ $you->getName() }},</p>
<p>Here is the Funeral Zone support rota for ({{ $when->format('D jS F') }}).</p>
<ul>
    @foreach($slot->getAssignees() as $assignee)
        <li>{{ $assignee->getName() }}</li>
    @endforeach
</ul>
</body>
</html>
