<h1>{{ $user->getName() }}</h1>
@forelse ($user->getOpenChats() as $key => $chat)
    @foreach ($chat as $message)
        <blockquote>
            {{ $message->message }}
            <small>{{ $message->nick }}</small>
        </blockquote>
        <br />
    @endforeach
@empty
    {{ Alert::danger('There are no open chats! :(') }}
@endforelse