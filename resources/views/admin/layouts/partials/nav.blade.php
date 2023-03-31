<nav class="mainmenu_wrapper">
    <ul class="mainmenu nav sf-menu">
        <li>
            <a href="{{ url('/') }}">@lang('nav.home')</a>
        </li>
        <li>
            <a href="{{ url('admin/enter-hymn') }}">Hymn</a>
            <ul>
                <li>
                    <a href="{{ url('admin/enter-hymn') }}">Enter New Hymn</a>
                </li>
                <li>
                    <a href="{{ url('admin/load-hymn') }}">Edit Hymn</a>
                </li>
            </ul>
        </li>
        <li>
            <a href="{{ route('superadmin-hinario') }}">Hinario</a>
        </li>
        <li>
            <a href="{{ url('admin/edit-person') }}">Person</a>
        </li>
        <li>
            <a href="{{ route('feedback') }}">Feedback</a>
        </li>
        <li>
            <a href="{{ url('admin/edit-book') }}">Books</a>
        </li>
        <li>
            <a href="{{ url('admin/edit-link') }}">Links</a>
        </li>
        <li>
            <a href="{{ url('admin/edit-musician-files') }}">Musician Resource Files</a>
        </li>
        <li>
            <a href="{{ url('admin/move-hymn-files') }}">Move Hymn Files</a>
        </li>
        <li>
            <a href="{{ url('admin/generate-hinario-zip-files') }}">Generate Hinario Zips</a>
        </li>
        <li>
            <a href="{{ url('admin/import-people-audio-files') }}">Import People Audio Files</a>
        </li>
    </ul>
</nav>
<!-- eof main nav -->
