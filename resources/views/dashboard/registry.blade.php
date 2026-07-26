<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        {{ $title }} | NorthPole Control Centre
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
            font-size: 18px;
            font-weight: 700;
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
                    rgba(62, 199, 232, 0.18),
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

        .hero h2 {
            margin: 0;
            font-size: clamp(36px, 6vw, 68px);
            line-height: 1;
            letter-spacing: -0.055em;
        }

        .hero-copy {
            max-width: 820px;
            margin: 20px 0 0;
            color: #9cb0c6;
            font-size: 15px;
            line-height: 1.7;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 20px;
        }

        .summary-card {
            padding: 20px;
            border: 1px solid rgba(154, 184, 218, 0.12);
            border-radius: 18px;
            background: rgba(11, 31, 51, 0.7);
        }

        .summary-card span {
            display: block;
            color: #849ab2;
            font-size: 12px;
        }

        .summary-card strong {
            display: block;
            margin-top: 12px;
            font-size: 30px;
            letter-spacing: -0.05em;
        }

        .registry {
            margin-top: 20px;
            padding: 28px;
        }

        .module-group + .module-group {
            margin-top: 16px;
        }

        .module-group {
            padding: 22px;
            border: 1px solid rgba(154, 184, 218, 0.12);
            border-radius: 20px;
            background:
                linear-gradient(
                    145deg,
                    rgba(14, 35, 57, 0.96),
                    rgba(9, 25, 43, 0.92)
                );
        }

        .group-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }

        .group-heading h3 {
            margin: 0;
            font-size: 18px;
            letter-spacing: -0.025em;
        }

        .group-heading span {
            padding: 6px 9px;
            border: 1px solid rgba(49, 215, 162, 0.25);
            border-radius: 999px;
            background: rgba(49, 215, 162, 0.1);
            color: #67e3bd;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .items {
            display: grid;
            gap: 9px;
        }

        .item {
            display: block;
            padding: 13px 14px;
            border: 1px solid rgba(154, 184, 218, 0.1);
            border-radius: 13px;
            background: rgba(11, 31, 51, 0.58);
            transition:
                border-color 160ms ease,
                background 160ms ease,
                transform 160ms ease;
        }

        .item-link:hover,
        .item-link:focus-visible {
            border-color: rgba(119, 219, 255, 0.42);
            background: rgba(17, 43, 68, 0.82);
            outline: none;
            transform: translateY(-1px);
        }

        .item-row {
            display: grid;
            grid-template-columns: minmax(180px, 0.6fr) minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .item-key {
            color: #dce9f6;
            font-size: 13px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .item-value {
            min-width: 0;
            color: #879db5;
            font-size: 12px;
            line-height: 1.6;
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
            overflow-wrap: anywhere;
        }

        .nested-title {
            margin: 0 0 10px;
            color: #7f95ae;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .nested-title:not(:first-child) {
            margin-top: 18px;
        }

        .empty {
            padding: 30px;
            border: 1px dashed rgba(154, 184, 218, 0.2);
            border-radius: 18px;
            color: #90a5bc;
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

        @media (max-width: 760px) {
            .shell {
                width: min(100% - 24px, 1440px);
                padding-top: 20px;
            }

            .topbar,
            .group-heading,
            .footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .hero,
            .registry {
                padding: 22px;
            }

            .summary-grid,
            .item-row,
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
                    <p>Runtime registry explorer</p>
                </div>
            </div>

            <a
                class="back-link"
                href="{{ route('control-centre') }}"
            >
                Back to Control Centre
            </a>
        </header>

        <section class="panel hero">
            <p class="eyebrow">Runtime registry</p>

            <h2>{{ $title }}</h2>

            <p class="hero-copy">
                {{ $description }}
            </p>
        </section>

        <section class="summary-grid">
            <article class="summary-card">
                <span>Contributing modules</span>
                <strong>{{ $summary['modules'] }}</strong>
            </article>

            <article class="summary-card">
                <span>Registered items</span>
                <strong>{{ $summary['items'] }}</strong>
            </article>
        </section>

        <section class="panel registry">
            @if ($groups === [])
                <div class="empty">
                    No runtime registrations were found.
                </div>
            @else
                @foreach ($groups as $module => $items)
                    <article class="module-group">
                        <div class="group-heading">
                            <h3>{{ $module }}</h3>

                            <span>
                                {{ is_array($items)
                                    ? count($items)
                                    : 0 }}
                                entries
                            </span>
                        </div>

                        @if ($registry === 'events')
                            <p class="nested-title">Published events</p>

                            <div class="items">
                                @forelse (
                                    $items['publishes'] ?? []
                                    as $event
                                )
                                    <a
                                        class="item item-link"
                                        href="{{ route(
                                            'control-centre.runtime.inspector.show',
                                            [
                                                'registry' => $registry,
                                                'module' => $module,
                                                'key' => $event,
                                            ],
                                        ) }}"
                                    >
                                        <code>{{ $event }}</code>
                                    </a>
                                @empty
                                    <div class="empty">
                                        No published events.
                                    </div>
                                @endforelse
                            </div>

                            <p class="nested-title">Event subscribers</p>

                            <div class="items">
                                @forelse (
                                    $items['subscribes'] ?? []
                                    as $event => $listeners
                                )
                                    <a
                                        class="item item-link"
                                        href="{{ route(
                                            'control-centre.runtime.inspector.show',
                                            [
                                                'registry' => $registry,
                                                'module' => $module,
                                                'key' => $event,
                                            ],
                                        ) }}"
                                    >
                                        <div class="item-row">
                                            <div class="item-key">
                                                {{ $event }}
                                            </div>

                                            <div class="item-value">
                                                @foreach (
                                                    $listeners
                                                    as $listener
                                                )
                                                    <div>
                                                        <code>
                                                            {{ $listener }}
                                                        </code>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="empty">
                                        No event subscribers.
                                    </div>
                                @endforelse
                            </div>
                        @else
                            <div class="items">
                                @foreach ($items as $key => $value)
                                    @php
                                        $registrationKey = null;

                                        if (is_string($key)) {
                                            $registrationKey = $key;
                                        } elseif (is_string($value)) {
                                            $registrationKey = $value;
                                        } elseif (is_array($value)) {
                                            foreach (
                                                [
                                                    'key',
                                                    'name',
                                                    'id',
                                                    'route',
                                                    'permission',
                                                    'capability',
                                                    'notification',
                                                    'job',
                                                    'class',
                                                ] as $candidate
                                            ) {
                                                $candidateValue =
                                                    $value[$candidate]
                                                        ?? null;

                                                if (
                                                    is_string(
                                                        $candidateValue
                                                    )
                                                    && trim(
                                                        $candidateValue
                                                    ) !== ''
                                                ) {
                                                    $registrationKey = trim(
                                                        $candidateValue
                                                    );

                                                    break;
                                                }
                                            }
                                        }

                                        $registrationKey ??= sprintf(
                                            'entry-%d',
                                            $key + 1,
                                        );
                                    @endphp

                                    <a
                                        class="item item-link"
                                        href="{{ route(
                                            'control-centre.runtime.inspector.show',
                                            [
                                                'registry' => $registry,
                                                'module' => $module,
                                                'key' => $registrationKey,
                                            ],
                                        ) }}"
                                    >
                                        @if (is_string($key))
                                            <div class="item-row">
                                                <div class="item-key">
                                                    {{ $key }}
                                                </div>

                                                <div class="item-value">
                                                    @if (is_array($value))
                                                        <dl
                                                            class="definition-grid"
                                                        >
                                                            @foreach (
                                                                $value
                                                                as $field =>
                                                                    $fieldValue
                                                            )
                                                                <dt>
                                                                    {{
                                                                        str(
                                                                            $field
                                                                        )
                                                                            ->replace(
                                                                                '_',
                                                                                ' '
                                                                            )
                                                                            ->title()
                                                                    }}
                                                                </dt>

                                                                <dd>
                                                                    {{
                                                                        is_scalar(
                                                                            $fieldValue
                                                                        )
                                                                            ? (
                                                                                is_bool(
                                                                                    $fieldValue
                                                                                )
                                                                                    ? (
                                                                                        $fieldValue
                                                                                            ? 'true'
                                                                                            : 'false'
                                                                                    )
                                                                                    : $fieldValue
                                                                            )
                                                                            : json_encode(
                                                                                $fieldValue
                                                                            )
                                                                    }}
                                                                </dd>
                                                            @endforeach
                                                        </dl>
                                                    @else
                                                        <code>
                                                            {{ $value }}
                                                        </code>
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif (is_array($value))
                                            <dl class="definition-grid">
                                                @foreach (
                                                    $value
                                                    as $field => $fieldValue
                                                )
                                                    <dt>
                                                        {{
                                                            str($field)
                                                                ->replace(
                                                                    '_',
                                                                    ' '
                                                                )
                                                                ->title()
                                                        }}
                                                    </dt>

                                                    <dd>
                                                        {{
                                                            is_scalar(
                                                                $fieldValue
                                                            )
                                                                ? (
                                                                    is_bool(
                                                                        $fieldValue
                                                                    )
                                                                        ? (
                                                                            $fieldValue
                                                                                ? 'true'
                                                                                : 'false'
                                                                        )
                                                                        : $fieldValue
                                                                )
                                                                : json_encode(
                                                                    $fieldValue
                                                                )
                                                        }}
                                                    </dd>
                                                @endforeach
                                            </dl>
                                        @else
                                            <code>{{ $value }}</code>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            @endif
        </section>

        <footer class="footer">
            <span>NorthPole Platform Runtime</span>

            <span>
                Live data generated from runtime module manifests
            </span>
        </footer>
    </main>
</body>
</html>