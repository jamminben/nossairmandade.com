@if ($pageNumber == 1)
    @foreach($stanzas as $stanza)
        @if ($loop->first)
            <div class="hymnstanza">
                <div class="hymn-bar-full">
                    <div class="hymn-bar-full">
                        @foreach($stanza->getLines() as $line)
                            @if ($loop->iteration == 1)
                                <div class="hymn-words">
                            @endif
                            @if ($loop->iteration == 1 || $loop->iteration == 2)
                                {{ $line }}<br>
                            @endif
                            @if ($loop->iteration == 2)
                                </div>
                            @endif
                            @if ($loop->iteration == 3)
                    </div>
                    <div class="hymn-bar-empty">
                                <div class="hymn-words">
                            @endif
                            @if ($loop->iteration == 3 || $loop->iteration == 4 || $loop->iteration == 5 || $loop->iteration == 6)
                                {{ $line }}<br>
                            @endif
                            @if ($loop->iteration == 6)
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @elseif ($loop->iteration == 2)
            <br>
            <div class="hymnstanza">
                <div class="hymn-bar-full">
                    <div class="hymn-words">
                        @foreach($stanza->getLines() as $line)
                            @if ($loop->index == 2)
                    </div>
                </div>
                <div class="hymn-bar-empty">
                    <div class="hymn-spacer">&nbsp;</div>
                </div>
                <div class="hymn-bar-full">
                    <div class="hymn-words">
                        @endif
                        {{ $line }}<br>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <br>
            <div class="hymnstanza">
                <div class="hymn-bar-empty">
                    <div class="hymn-bar-empty">
                        <div class="hymn-words">
                            @foreach($stanza->getLines() as $line)
                                {{ $line }}<br>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@else
    @foreach($stanzas as $stanza)
        @if ($loop->first)
            <div class="hymnstanza">
                <div class="hymn-bar-full">
                    <div class="hymn-words">
                        @foreach($stanza->getLines() as $line)
                            @if ($loop->index == 2)
                    </div>
                </div>
                <div class="hymn-bar-empty">
                    <div class="hymn-spacer">&nbsp;</div>
                </div>
                <div class="hymn-bar-full">
                    <div class="hymn-words">
                        @endif
                        {{ $line }}<br>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <br>
            <div class="hymnstanza">
                <div class="hymn-bar-empty">
                    <div class="hymn-words">
                        @foreach($stanza->getLines() as $line)
                            {{ $line }}<br>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endif
