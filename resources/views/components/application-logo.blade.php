<svg viewBox="0 0 316 316" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
        <title>DFahMy Eco Garden mark</title>
        <defs>
            <style>
                .ring {
                    stroke: #4b4b4b;
                    stroke-width: 1.8;
                    fill: none;
                }

                .leaf-dark {
                    fill: #0d665c;
                }

                .leaf-mid {
                    fill: #18a85f;
                }

                .house {
                    fill: #ffffff;
                }

                .house-shadow {
                    fill: #165f48;
                }
            </style>
        </defs>

        <circle cx="32" cy="32" r="30" class="ring" />
        <circle cx="32" cy="32" r="27" class="ring" opacity="0.8" />

        <g transform="translate(32 24)">
            <g transform="rotate(-28)">
                <ellipse cx="-10" cy="-8" rx="4.6" ry="8.8" class="leaf-dark"
                    transform="rotate(-15)" />
                <ellipse cx="-16" cy="2" rx="3.8" ry="7.6" class="leaf-mid"
                    transform="rotate(18)" />
                <ellipse cx="-9" cy="12" rx="4.2" ry="8.4" class="leaf-dark"
                    transform="rotate(-7)" />
            </g>
            <g transform="rotate(34)">
                <ellipse cx="10" cy="-8" rx="4.6" ry="8.8" class="leaf-mid"
                    transform="rotate(15)" />
                <ellipse cx="16" cy="2" rx="3.8" ry="7.6" class="leaf-dark"
                    transform="rotate(-18)" />
                <ellipse cx="9" cy="12" rx="4.2" ry="8.4" class="leaf-mid"
                    transform="rotate(7)" />
            </g>
            <g transform="rotate(0)">
                <ellipse cx="0" cy="-15" rx="4.2" ry="8.8" class="leaf-mid" />
                <ellipse cx="-18" cy="-2" rx="4.1" ry="7.7" class="leaf-dark"
                    transform="rotate(-56)" />
                <ellipse cx="18" cy="-2" rx="4.1" ry="7.7" class="leaf-dark"
                    transform="rotate(56)" />
            </g>
        </g>

        <g transform="translate(20 24)">
            <path d="M12 5.2 4.5 12.1v14.5h15V12.1L12 5.2Z" class="house-shadow" />
            <path d="M12 0 1.2 9.8v0.2h4v13h15.6v-13h4v-0.2L12 0Z" class="house" />
            <path d="M8 15.5h8v6.8H8z" class="house-shadow" opacity="0.9" />
            <path d="M9.7 5.8 12 3.7l2.3 2.1v7.7H9.7V5.8Z" class="house-shadow" opacity="0.35" />
        </g>
    </svg>
</svg>
