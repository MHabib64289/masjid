/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./admin/**/*.php",
    "./user/**/*.php",
    "./includes/**/*.php",
    "./*.php",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#2C3E50',
          dark: '#1a252f',
          light: '#34495E',
        },
        secondary: {
          DEFAULT: '#34495E',
          dark: '#2c3e50',
          light: '#425b76',
        },
        accent: {
          DEFAULT: '#16A085',
          dark: '#138a72',
          light: '#1abc9c',
        },
        'light-bg': '#F0F2F5',
      },
      fontFamily: {
        sans: ['Poppins', 'ui-sans-serif', 'system-ui'],
      },
      spacing: {
        '72': '18rem',
        '84': '21rem',
        '96': '24rem',
      },
      maxWidth: {
        '8xl': '88rem',
        '9xl': '96rem',
      },
      minHeight: {
        '10': '2.5rem',
        '12': '3rem',
        '16': '4rem',
      },
      boxShadow: {
        'subtle': '0 2px 4px rgba(0,0,0,0.05)',
        'soft': '0 4px 6px -1px rgba(0,0,0,0.05)',
      },
      borderRadius: {
        'xl': '1rem',
        '2xl': '2rem',
      },
      transitionDuration: {
        '400': '400ms',
      },
      zIndex: {
        '60': '60',
        '70': '70',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms')({
      strategy: 'class',
    }),
  ],
}
