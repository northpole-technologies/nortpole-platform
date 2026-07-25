<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Runtime Dependency Graph | NorthPole Control Centre</title>

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
            max-width: 780px;
            margin: 20px 0 0;
            color: #9cb0c6;
            font-size: 15px;
            line-height: 1.7;
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
            letter-spacing: -0.05em;
        }

        .graph {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-top: 20px;
            padding: 28px;
        }

        .node {
            position: relative;
            min-height: 190px;
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

        .node::before {
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            border-radius: 20px 0 0 20px;
            background: #31d7a2;
            content: "";
        }

        .node.disabled::before {
            background: #f2a84b;
        }

        .node h3 {
            margin: 0;
            font-size: 19px;
        }

        .node-meta {
            margin: 7px 0 0;
            color: #768ca6;
            font-size: 12px;
        }

        .badge {
            display: inline-flex;
            margin-top: 15px;
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

        .disabled .badge {
            border-color: rgba(242, 168, 75, 0.26);
            background: rgba(242, 168, 75, 0.09);
            color: #ffc779;
        }

        .dependencies {
            margin-top: 18px;
        }

        .dependencies h4 {
            margin: 0 0 9px;
            color: #7f95ae;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .dependency {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 10px;
            border-radius: 10px;
            background: rgba(129, 160, 195, 0.08);
            color: #a4b7cb;
            font-size: 11px;
        }

        .dependency + .dependency {
            margin-top: 7px;
        }

        .dependency strong {
            color: #d7e5f3;
        }

        .no-dependencies {
            color: #7188a2;
            font-size: 11px;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 26px;
            color: #687e98;
            font-size: 12px;
        }

        @media (max-width: 720px) {
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
            .graph {
                padding: 22px;
            }

            .summary-grid {
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
                    <p>Runtime dependency explorer</p>
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
            <p class="eyebrow">Runtime architecture</p>

            <h2>Dependency Graph</h2>

            <p class="hero-copy">
                Live module relationships generated from the runtime
                manifests. Each dependency shows which module requires
                another module and the declared version constraint.
            </p>
        </section>

        <section class="summary-grid">
            <article class="summary-card">
                <span>Runtime modules</span>
                <strong>{{ $summary['modules'] }}</strong>
            </article>

            <article class="summary-card">
                <span>Enabled modules</span>
                <strong>{{ $summary['enabled'] }}</strong>
            </article>

            <article class="summary-card">
                <span>Dependency relationships</span>
                <strong>{{ $summary['dependencies'] }}</strong>
            </article>
        </section>

        <section class="panel graph">
            @foreach ($nodes as $node)
                @php
                    $dependencies = array_values(
                        array_filter(
                            $edges,
                            static fn (array $edge): bool =>
                                $edge['source'] === $node['id'],
                        ),
                    );
                @endphp

                <a
                    href="{{ route(
                        'control-centre.modules.show',
                        ['slug' => $node['id']],
                    ) }}"
                >
                    <article
                        class="node
                            {{ $node['enabled'] ? '' : 'disabled' }}"
                    >
                        <h3>{{ $node['name'] }}</h3>

                        <p class="node-meta">
                            {{ $node['id'] }}
                            ·
                            v{{ $node['version'] }}
                        </p>

                        <span class="badge">
                            {{ $node['enabled']
                                ? 'Enabled'
                                : 'Disabled' }}
                        </span>

                        <div class="dependencies">
                            <h4>Depends on</h4>

                            @if ($dependencies === [])
                                <div class="no-dependencies">
                                    No dependencies declared
                                </div>
                            @else
                                @foreach ($dependencies as $dependency)
                                    <div class="dependency">
                                        <strong>
                                            {{ $dependency['target'] }}
                                        </strong>

                                        <span>
                                            {{ $dependency['constraint'] }}
                                        </span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </article>
                </a>
            @endforeach
        </section>

        <footer class="footer">
            <span>NorthPole Platform Runtime</span>

            <span>
                Live dependency data generated from module manifests
            </span>
        </footer>
    </main>
</body>
</html>