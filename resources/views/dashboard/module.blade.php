<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        {{ $module['name'] }} | NorthPole Control Centre
    </title>

    <style>
        :root {
            color-scheme: dark;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            background: #07111f;
            color: #e8eef7;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(
                    circle at top left,
                    rgba(28, 108, 137, 0.28),
                    transparent 34rem
                ),
                radial-gradient(
                    circle at top right,
                    rgba(69, 52, 141, 0.24),
                    transparent 30rem
                ),
                #07111f;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        code {
            overflow-wrap: anywhere;
            color: #b9d9f4;
            font-family:
                "Cascadia Code",
                "SFMono-Regular",
                Consolas,
                monospace;
            font-size: 12px;
        }

        .shell {
            width: min(1440px, calc(100% - 40px));
            margin: 0 auto;
            padding: 36px 0 70px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 28px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            display: grid;
            width: 48px;
            height: 48px;
            place-items: center;
            border: 1px solid rgba(119, 219, 255, 0.4);
            border-radius: 16px;
            background:
                linear-gradient(
                    145deg,
                    rgba(62, 199, 232, 0.22),
                    rgba(115, 91, 255, 0.18)
                );
            font-size: 22px;
        }

        .brand h1 {
            margin: 0;
            font-size: 20px;
            letter-spacing: -0.02em;
        }

        .brand p {
            margin: 4px 0 0;
            color: #92a5bc;
            font-size: 13px;
        }

        .back-link {
            padding: 10px 14px;
            border: 1px solid rgba(151, 177, 206, 0.18);
            border-radius: 12px;
            background: rgba(13, 28, 46, 0.72);
            color: #aabbd0;
            font-size: 13px;
            transition:
                border-color 160ms ease,
                transform 160ms ease;
        }

        .back-link:hover {
            border-color: rgba(119, 219, 255, 0.45);
            transform: translateY(-1px);
        }

        .panel {
            border: 1px solid rgba(151, 177, 206, 0.14);
            border-radius: 24px;
            background: rgba(10, 24, 40, 0.82);
            box-shadow:
                0 30px 80px rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(18px);
        }

        .hero {
            position: relative;
            overflow: hidden;
            padding: 34px;
        }

        .hero::after {
            position: absolute;
            right: -70px;
            bottom: -100px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background:
                radial-gradient(
                    circle,
                    rgba(46, 214, 161, 0.18),
                    transparent 68%
                );
            content: "";
        }

        .eyebrow {
            margin: 0 0 14px;
            color: #7f95ae;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
        }

        .module-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 22px;
        }

        .module-heading h2 {
            margin: 0;
            font-size: clamp(36px, 6vw, 68px);
            line-height: 1;
            letter-spacing: -0.055em;
        }

        .module-slug {
            margin: 10px 0 0;
            color: #7f95ae;
            font-size: 14px;
        }

        .badge {
            padding: 8px 12px;
            border: 1px solid rgba(49, 215, 162, 0.25);
            border-radius: 999px;
            background: rgba(49, 215, 162, 0.1);
            color: #67e3bd;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .badge.disabled {
            border-color: rgba(242, 168, 75, 0.26);
            background: rgba(242, 168, 75, 0.09);
            color: #ffc779;
        }

        .description {
            max-width: 820px;
            margin: 24px 0 0;
            color: #9cb0c6;
            font-size: 15px;
            line-height: 1.7;
        }

        .identity-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 30px;
        }

        .identity {
            padding: 15px;
            border: 1px solid rgba(154, 184, 218, 0.11);
            border-radius: 15px;
            background: rgba(11, 31, 51, 0.62);
        }

        .identity span {
            display: block;
            margin-bottom: 7px;
            color: #7188a2;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .identity strong {
            color: #dbe8f5;
            font-size: 13px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin-top: 20px;
        }

        .summary-card {
            min-height: 106px;
            padding: 17px;
            border: 1px solid rgba(154, 184, 218, 0.12);
            border-radius: 18px;
            background: rgba(11, 31, 51, 0.7);
        }

        .summary-card span {
            display: block;
            min-height: 32px;
            color: #849ab2;
            font-size: 11px;
            line-height: 1.4;
        }

        .summary-card strong {
            display: block;
            margin-top: 10px;
            font-size: 26px;
            letter-spacing: -0.05em;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .section {
            padding: 25px;
        }

        .section.wide {
            grid-column: 1 / -1;
        }

        .section h3 {
            margin: 0;
            font-size: 17px;
            letter-spacing: -0.025em;
        }

        .section-copy {
            margin: 6px 0 20px;
            color: #7188a2;
            font-size: 12px;
        }

        .items {
            display: grid;
            gap: 9px;
        }

        .item {
            padding: 13px 14px;
            border: 1px solid rgba(154, 184, 218, 0.1);
            border-radius: 13px;
            background: rgba(11, 31, 51, 0.58);
        }

        .item-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .item-heading strong {
            color: #dce9f6;
            font-size: 13px;
        }

        .item-heading span {
            color: #6eddbb;
            font-size: 11px;
        }

        .item-detail {
            margin: 8px 0 0;
            color: #879db5;
            font-size: 12px;
            line-height: 1.5;
        }

        .chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .chip {
            padding: 8px 10px;
            border-radius: 10px;
            background: rgba(129, 160, 195, 0.09);
            color: #a4b7cb;
            font-size: 11px;
        }

        .definition-grid {
            display: grid;
            grid-template-columns: 130px minmax(0, 1fr);
            gap: 8px 16px;
            margin: 0;
        }

        .definition-grid dt {
            color: #7188a2;
            font-size: 11px;
        }

        .definition-grid dd {
            min-width: 0;
            margin: 0;
            color: #c9d8e8;
            font-size: 12px;
        }

        .empty {
            padding: 22px;
            border: 1px dashed rgba(154, 184, 218, 0.18);
            border-radius: 14px;
            color: #7188a2;
            font-size: 12px;
            text-align: center;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 26px;
            color: #687e98;
            font-size: 12px;
        }

        @media (max-width: 1080px) {
            .summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .identity-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .shell {
                width: min(100% - 24px, 1440px);
                padding-top: 20px;
            }

            .topbar,
            .module-heading,
            .footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .hero,
            .section {
                padding: 22px;
            }

            .summary-grid,
            .identity-grid,
            .content-grid {
                grid-template-columns: 1fr;
            }

            .section.wide {
                grid-column: auto;
            }

            .definition-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <main class="shell">
        <header class="topbar">
            <div class="brand">
                <div
                    class="brand-mark"
                    aria-hidden="true"
                >
                    NP
                </div>

                <div>
                    <h1>NorthPole Control Centre</h1>
                    <p>Runtime module inspector</p>
                </div>
            </div>

            <a
                class="back-link"
                href="{{ route('control-centre') }}"
            >
                ← Back to Control Centre
            </a>
        </header>

        <section class="panel hero">
            <p class="eyebrow">Runtime module</p>

            <div class="module-heading">
                <div>
                    <h2>{{ $module['name'] }}</h2>

                    <p class="module-slug">
                        {{ $module['slug'] }}
                        ·
                        v{{ $module['version'] }}
                    </p>
                </div>

                <span
                    class="badge
                        {{ $module['enabled'] ? '' : 'disabled' }}"
                >
                    {{ $module['enabled']
                        ? 'Enabled'
                        : 'Disabled' }}
                </span>
            </div>

            <p class="description">
                {{ $module['description']
                    ?? 'No module description provided.' }}
            </p>

            <div class="identity-grid">
                <div class="identity">
                    <span>Version</span>
                    <strong>{{ $module['version'] }}</strong>
                </div>

                <div class="identity">
                    <span>Module path</span>
                    <code>{{ $module['path'] }}</code>
                </div>

                <div class="identity">
                    <span>Manifest</span>
                    <code>{{ $module['manifest_path'] }}</code>
                </div>

                <div class="identity">
                    <span>Status</span>
                    <strong>
                        {{ $module['enabled']
                            ? 'Runtime enabled'
                            : 'Runtime disabled' }}
                    </strong>
                </div>
            </div>
        </section>

        <section class="summary-grid">
            @foreach ($summary as $label => $value)
                <article class="summary-card">
                    <span>
                        {{ str($label)->replace('_', ' ')->title() }}
                    </span>

                    <strong>{{ $value }}</strong>
                </article>
            @endforeach
        </section>

        <div class="content-grid">
            <section class="panel section">
                <h3>Dependencies</h3>
                <p class="section-copy">
                    Modules required by this runtime component.
                </p>

                @if ($module['dependencies'] === [])
                    <div class="empty">
                        No module dependencies declared.
                    </div>
                @else
                    <div class="items">
                        @foreach (
                            $module['dependencies']
                            as $dependency => $constraint
                        )
                            <div class="item">
                                <div class="item-heading">
                                    <strong>{{ $dependency }}</strong>
                                    <span>{{ $constraint }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel section">
                <h3>Providers</h3>
                <p class="section-copy">
                    Laravel service providers loaded by the module.
                </p>

                @if ($module['providers'] === [])
                    <div class="empty">
                        No service providers declared.
                    </div>
                @else
                    <div class="items">
                        @foreach ($module['providers'] as $provider)
                            <div class="item">
                                <code>{{ $provider }}</code>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel section">
                <h3>Commands</h3>
                <p class="section-copy">
                    Runtime commands and their registered handlers.
                </p>

                @if ($module['commands'] === [])
                    <div class="empty">
                        No commands declared.
                    </div>
                @else
                    <div class="items">
                        @foreach (
                            $module['commands']
                            as $command => $handler
                        )
                            <div class="item">
                                <div class="item-heading">
                                    <strong>{{ $command }}</strong>
                                </div>

                                <p class="item-detail">
                                    <code>{{ $handler }}</code>
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel section">
                <h3>Queries</h3>
                <p class="section-copy">
                    Runtime queries and their registered handlers.
                </p>

                @if ($module['queries'] === [])
                    <div class="empty">
                        No queries declared.
                    </div>
                @else
                    <div class="items">
                        @foreach (
                            $module['queries']
                            as $query => $handler
                        )
                            <div class="item">
                                <div class="item-heading">
                                    <strong>{{ $query }}</strong>
                                </div>

                                <p class="item-detail">
                                    <code>{{ $handler }}</code>
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel section">
                <h3>Permissions</h3>
                <p class="section-copy">
                    Access permissions contributed by this module.
                </p>

                @if ($module['permissions'] === [])
                    <div class="empty">
                        No permissions declared.
                    </div>
                @else
                    <div class="chips">
                        @foreach (
                            $module['permissions']
                            as $permission
                        )
                            <span class="chip">
                                {{ is_array($permission)
                                    ? ($permission['key'] ?? 'Unknown')
                                    : $permission }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel section">
                <h3>Capabilities</h3>
                <p class="section-copy">
                    Platform capabilities exposed by this module.
                </p>

                @if ($module['capabilities'] === [])
                    <div class="empty">
                        No capabilities declared.
                    </div>
                @else
                    <div class="chips">
                        @foreach (
                            $module['capabilities']
                            as $capability
                        )
                            <span class="chip">
                                {{ $capability }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel section">
                <h3>Published events</h3>
                <p class="section-copy">
                    Domain and platform events emitted by the module.
                </p>

                @if ($module['published_events'] === [])
                    <div class="empty">
                        No published events declared.
                    </div>
                @else
                    <div class="chips">
                        @foreach (
                            $module['published_events']
                            as $event
                        )
                            <span class="chip">{{ $event }}</span>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel section">
                <h3>Event subscribers</h3>
                <p class="section-copy">
                    Events consumed by registered listener classes.
                </p>

                @if ($module['event_subscribers'] === [])
                    <div class="empty">
                        No event subscribers declared.
                    </div>
                @else
                    <div class="items">
                        @foreach (
                            $module['event_subscribers']
                            as $event => $listeners
                        )
                            <div class="item">
                                <div class="item-heading">
                                    <strong>{{ $event }}</strong>
                                    <span>
                                        {{ count($listeners) }}
                                        listener(s)
                                    </span>
                                </div>

                                @foreach ($listeners as $listener)
                                    <p class="item-detail">
                                        <code>{{ $listener }}</code>
                                    </p>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel section">
                <h3>Navigation</h3>
                <p class="section-copy">
                    Navigation entries contributed to the platform.
                </p>

                @if ($module['navigation'] === [])
                    <div class="empty">
                        No navigation items declared.
                    </div>
                @else
                    <div class="items">
                        @foreach (
                            $module['navigation']
                            as $navigation
                        )
                            <div class="item">
                                <dl class="definition-grid">
                                    @foreach (
                                        $navigation
                                        as $key => $value
                                    )
                                        <dt>
                                            {{ str($key)
                                                ->replace('_', ' ')
                                                ->title() }}
                                        </dt>

                                        <dd>
                                            {{ is_scalar($value)
                                                ? $value
                                                : json_encode($value) }}
                                        </dd>
                                    @endforeach
                                </dl>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel section">
                <h3>Routes and resources</h3>
                <p class="section-copy">
                    Route, view, migration, and configuration resources.
                </p>

                <div class="item">
                    <dl class="definition-grid">
                        @foreach (
                            $module['routes']
                            as $type => $route
                        )
                            <dt>{{ str($type)->title() }} routes</dt>
                            <dd><code>{{ $route }}</code></dd>
                        @endforeach

                        <dt>Views</dt>
                        <dd>
                            <code>
                                {{ $module['views'] ?? 'Not declared' }}
                            </code>
                        </dd>

                        <dt>Migrations</dt>
                        <dd>
                            <code>
                                {{ $module['migrations']
                                    ?? 'Not declared' }}
                            </code>
                        </dd>

                        @foreach (
                            $module['configuration']
                            as $key => $configuration
                        )
                            <dt>
                                Configuration:
                                {{ $key }}
                            </dt>

                            <dd>
                                <code>{{ $configuration }}</code>
                            </dd>
                        @endforeach
                    </dl>
                </div>
            </section>

            <section class="panel section">
                <h3>Settings</h3>
                <p class="section-copy">
                    Configurable settings defined by the manifest.
                </p>

                @if ($module['settings'] === [])
                    <div class="empty">
                        No settings declared.
                    </div>
                @else
                    <div class="items">
                        @foreach ($module['settings'] as $setting)
                            <div class="item">
                                <dl class="definition-grid">
                                    @foreach (
                                        $setting
                                        as $key => $value
                                    )
                                        <dt>
                                            {{ str($key)
                                                ->replace('_', ' ')
                                                ->title() }}
                                        </dt>

                                        <dd>
                                            {{ is_scalar($value)
                                                ? (
                                                    is_bool($value)
                                                        ? ($value ? 'true' : 'false')
                                                        : $value
                                                )
                                                : json_encode($value) }}
                                        </dd>
                                    @endforeach
                                </dl>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel section">
                <h3>Notifications</h3>
                <p class="section-copy">
                    Notifications sent through runtime channels.
                </p>

                @if ($module['notifications'] === [])
                    <div class="empty">
                        No notifications declared.
                    </div>
                @else
                    <div class="items">
                        @foreach (
                            $module['notifications']
                            as $notification
                        )
                            <div class="item">
                                <div class="item-heading">
                                    <strong>
                                        {{ $notification['name'] }}
                                    </strong>

                                    <span>
                                        {{ implode(
                                            ', ',
                                            $notification['channels'],
                                        ) }}
                                    </span>
                                </div>

                                <p class="item-detail">
                                    <code>
                                        {{ $notification['class'] }}
                                    </code>
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel section">
                <h3>Scheduled jobs</h3>
                <p class="section-copy">
                    Scheduled runtime work declared by the module.
                </p>

                @if ($module['scheduled_jobs'] === [])
                    <div class="empty">
                        No scheduled jobs declared.
                    </div>
                @else
                    <div class="items">
                        @foreach (
                            $module['scheduled_jobs']
                            as $job
                        )
                            <div class="item">
                                <div class="item-heading">
                                    <strong>
                                        {{ $job['frequency'] }}
                                    </strong>

                                    <span>
                                        {{ $job['at'] ?? 'Automatic' }}
                                    </span>
                                </div>

                                <p class="item-detail">
                                    <code>{{ $job['class'] }}</code>
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <footer class="footer">
            <span>NorthPole Platform Runtime</span>

            <span>
                Module metadata generated from the runtime manifest
            </span>
        </footer>
    </main>
</body>
</html>