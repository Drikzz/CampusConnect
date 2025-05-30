const defaultTheme = require('tailwindcss/defaultTheme')
const animate = require("tailwindcss-animate")

/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: ["class"],
  safelist: ["dark"],
  prefix: "",
  
  content: [
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
    "./storage/framework/views/*.php",
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.{js,jsx,vue}",
  ],
  
  theme: {
  	container: {
  		center: true,
  		padding: '2rem',
  		screens: {
  			'2xl': '1400px'
  		}
  	},
  	extend: {
  		fontFamily: {
  			sans: [
  				'Figtree',
                    ...defaultTheme.fontFamily.sans
                ],
  			Header: [
  				'FontSpring-hvy',
  				'sans-serif'
  			],
  			Footer: [
  				'FontSpring-demi',
  				'sans-serif'
  			],
  			'FontSpring-bold': [
  				'FontSpring-bold',
  				'sans-serif'
  			],
  			'FontSpring-extra-bold': [
  				'FontSpring-extra-bold',
  				'sans-serif'
  			],
  			Satoshi: [
  				'Satoshi',
  				'sans-serif'
  			],
  			'Satoshi-bold': [
  				'Satoshi-bold',
  				'sans-serif'
  			],
  			'FontSpring-bold-oblique': [
  				'FontSpring-bold-oblique',
  				'sans-serif'
  			]
  		},
  		colors: {
  			'primary-color': '#8D0A0A',
  			footer: '#F0F0F0',
  			red: '#FF0000',
  			border: 'hsl(var(--border))',
  			input: 'hsl(var(--input))',
  			ring: 'hsl(var(--ring))',
  			background: 'hsl(var(--background))',
  			foreground: 'hsl(var(--foreground))',
  			primary: {
  				DEFAULT: 'hsl(var(--primary))',
  				foreground: 'hsl(var(--primary-foreground))'
  			},
  			secondary: {
  				DEFAULT: 'hsl(var(--secondary))',
  				foreground: 'hsl(var(--secondary-foreground))'
  			},
  			destructive: {
  				DEFAULT: 'hsl(var(--destructive))',
  				foreground: 'hsl(var(--destructive-foreground))'
  			},
  			muted: {
  				DEFAULT: 'hsl(var(--muted))',
  				foreground: 'hsl(var(--muted-foreground))'
  			},
  			accent: {
  				DEFAULT: 'hsl(var(--accent))',
  				foreground: 'hsl(var(--accent-foreground))'
  			},
  			popover: {
  				DEFAULT: 'hsl(var(--popover))',
  				foreground: 'hsl(var(--popover-foreground))'
  			},
  			card: {
  				DEFAULT: 'hsl(var(--card))',
  				foreground: 'hsl(var(--card-foreground))'
  			},
  			chart: {
  				'1': 'hsl(var(--chart-1))',
  				'2': 'hsl(var(--chart-2))',
  				'3': 'hsl(var(--chart-3))',
  				'4': 'hsl(var(--chart-4))',
  				'5': 'hsl(var(--chart-5))'
  			}
  		},
  		borderRadius: {
  			xl: 'calc(var(--radius) + 4px)',
  			lg: 'var(--radius)',
  			md: 'calc(var(--radius) - 2px)',
  			sm: 'calc(var(--radius) - 4px)'
  		},
  		keyframes: {
  			'accordion-down': {
  				from: {
  					height: 0
  				},
  				to: {
  					height: 'var(--radix-accordion-content-height)'
  				}
  			},
  			'accordion-up': {
  				from: {
  					height: 'var(--radix-accordion-content-height)'
  				},
  				to: {
  					height: 0
  				}
  			},
  			'collapsible-down': {
  				from: {
  					height: 0
  				},
  				to: {
  					height: 'var(--radix-collapsible-content-height)'
  				}
  			},
  			'collapsible-up': {
  				from: {
  					height: 'var(--radix-collapsible-content-height)'
  				},
  				to: {
  					height: 0
  				}
  			},
            'toast-slide-in-bottom': {
                '0%': { transform: 'translateY(100%)', opacity: 0 },
                '100%': { transform: 'translateY(0)', opacity: 1 }
            },
            'toast-slide-out-right': {
                '0%': { transform: 'translateX(0)', opacity: 1 },
                '100%': { transform: 'translateX(100%)', opacity: 0 }
            },
            'toast-swipe-out': {
                '0%': { transform: 'translateX(var(--reka-toast-swipe-end-x))', opacity: 1 },
                '100%': { transform: 'translateX(calc(100% + 32px))', opacity: 0 }
            }
  		},
  		animation: {
  			'accordion-down': 'accordion-down 0.2s ease-out',
  			'accordion-up': 'accordion-up 0.2s ease-out',
  			'collapsible-down': 'collapsible-down 0.2s ease-in-out',
  			'collapsible-up': 'collapsible-up 0.2s ease-in-out',
            'toast-slide-in': 'toast-slide-in-bottom 0.2s ease-out',
            'toast-slide-out': 'toast-slide-out-right 0.2s ease-in forwards',
            'toast-swipe-out': 'toast-swipe-out 0.2s ease-out forwards'
  		},
		backfaceVisibility: ['hidden'],
		transform: ['gpu'],
  	}
  },
  plugins: [animate, require("tailwindcss-animate")],
}