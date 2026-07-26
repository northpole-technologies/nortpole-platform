<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Runtime Search | NorthPole Control Centre</title>

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
            color: #b9d9f4;
            font-family:
                "Cascadia Code",
                "SFMono-Regular",
                Consolas,
                monospace;
            overflow-wrap: anywhere;
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
            padding: 34px;
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
            max-width: 760px;
            margin: 20px 0 0;
            color: #9cb0c6;
            font-size: 15px;
            line-height: 1.7;
        }

        .search-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 220px auto;
            gap: 12px;
            margin-top: 28px;
        }

        input,
        select,
        button {
            min-height: 48px;
            border: 1px solid rgba(154, 184, 218, 0.18);
            border-radius: 13px;
            font: inherit;
        }

        input,
        select {
            padding: 0 14px;
            background: rgba(8, 24, 42, 0.9);
            color: #e8eef7;
        }

        button {
            padding: 0 22px;
            border-color: rgba(119, 219, 255, 0.38);
            background:
                linear-gradient(
                    145deg,
                    rgba(62, 199, 232, 0.26),
                    rgba(115, 91, 255, 0.22)
                );
            color: #e8eef7;
            cursor: pointer;
            font-weight: 700;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
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
        }

        .results {
            margin-top: 20px;
            padding: 28px;
        }

        .module-group + .module-group {
            margin-top: 18px;
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

        .module-group h3 {
            margin: 0 0 18px;
            font-size: 20px;
        }

        .registry-group + .registry-group {
            margin-top: 22px;
        }

        .registry-heading {
            margin: 0 0 10px;
            color: #67e3bd;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .items {
            display: grid;
            gap: 9px;
        }

        .item {
            display: grid;
            grid-template-columns: minmax(190px, 0.55fr) minmax(0, 1fr);
            gap: 18px;
            padding: 14px;
            border: 1px solid rgba(154, 184, 218, 0.1);
            border-radius: 13px;
            background: rgba(11, 31, 51, 0.58);
        }

        .item-key {
            color: #dce9f6;
            font-size: 13px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .item-value {
            color: #879db5;
            font-size: 12px;
            line-height: 1.6;
            overflow-wrap: anywhere;
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
            .footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .hero,
            .results {
                padding: 22px;
            }

            .search-form,
            .summary-grid,
            .item {
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
                    <p>Runtime search</p>
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
            <p class="eyebrow">Runtime explorer</p>

            <h2>Runtime Search</h2>

            <p class="hero-copy">
                Search commands, queries, agents, events, permissions,
                capabilities, navigation, notifications and scheduled jobs.
            </p>

            <form
                class="search-form"
                method="GET"
                action="{{ route('control-centre.runtime.search') }}"
            >
                <input
                    type="search"
                    name="q"
                    value="{{ $query }}"
                    placeholder="Search runtime registrations"
                    aria-label="Search runtime registrations"
                >

                <select
                    name="module"
                    aria-label="Filter by module"
                >
                    <option value="">All modules</option>

                    @foreach ($moduleOptions as $slug => $name)
                        <option
                            value="{{ $slug }}"
                            @selected($selectedModule === $slug)
                        >
                            {{ $name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit">
                    Search
                </button>
            </form>
        </section>

        <section class="summary-grid">
            <article class="summary-card">
                <span>Matches</span>
                <strong>{{ $summary['matches'] }}</strong>
            </article>

            <article class="summary-card">
                <span>Modules</span>
                <strong>{{ $summary['modules'] }}</strong>
            </article>

            <article class="summary-card">
                <span>Registries</span>
                <strong>{{ $summary['registries'] }}</strong>
            </article>
        </section>

        <section class="panel results">
            @if ($query === '')
                <div class="empty">
                    Enter a search term to inspect the runtime.
                </div>
            @elseif ($groups === [])
                <div class="empty">
                    No matching runtime registrations were found.
                </div>
            @else
                @foreach ($groups as $module => $registries)
                    <article class="module-group">
                        <h3>{{ $module }}</h3>

                        @foreach ($registries as $registry => $items)
                            <section class="registry-group">
                                <p class="registry-heading">
                                    {{
                                        str($registry)
                                            ->replace('-', ' ')
                                            ->title()
                                    }}
                                </p>

                                <div class="items">
                                    @foreach ($items as $item)
                                        <div class="item">
                                            <div class="item-key">
                                                {{ $item['key'] }}
                                            </div>

                                            <div class="item-value">
                                                @if (
                                                    is_array(
                                                        $item['value']
                                                    )
                                                )
                                                    <code>
                                                        {{
                                                            json_encode(
                                                                $item['value'],
                                                                JSON_UNESCAPED_SLASHES
                                                                | JSON_UNESCAPED_UNICODE
                                                            )
                                                        }}
                                                    </code>
                                                @else
                                                    <code>
                                                        {{
                                                            is_bool(
                                                                $item['value']
                                                            )
                                                                ? (
                                                                    $item['value']
                                                                        ? 'true'
                                                                        : 'false'
                                                                )
                                                                : $item['value']
                                                        }}
                                                    </code>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </article>
                @endforeach
            @endif
        </section>

        <footer class="footer">
            <span>NorthPole Platform Runtime</span>

            <span>
                Search generated from runtime module manifests
            </span>
        </footer>
    </main>
</body>
</html>