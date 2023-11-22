<h1>
    Ciao Admin!
</h1>

<p>
    Hai ricevuto un nuovo messaggio da: <br><br>
    <strong>Name:</strong> {{ $lead->name }} <br>
    <strong>Email:</strong> {{ $lead->email }} <br>
</p>

<p>
    <strong>Message:</strong> <br>
    {{ $lead->message }}
</p>
