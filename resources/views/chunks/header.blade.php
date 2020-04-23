<nav class="navbar navbar-light bg-light d-flex justify-content-between flex-row">
    <ul class="navbar-nav">
        <li class="nav-item">
            <router-link to="/tariffs">
                <span class="nav-link">Тарифы</span>
            </router-link>
        </li>
    </ul>

    <div class="user d-flex">
        {{ $user->name }}

        <logout-link></logout-link>
    </div>
</nav>