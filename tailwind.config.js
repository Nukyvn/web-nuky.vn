// tailwind.config.js
export default {
  content: [
    "./*.php",
    "./admin/**/*.php",
    "./public/**/*.php",
    "./**/*.php",
    "./**/*.html",
  ],
  theme: {
    extend: {
      colors: {
        primary: "#10b981",
        secondary: "#3b82f6",
        accent: "#f59e0b",
        heroOverlay: "rgba(255, 255, 255, 0.2)", // overlay trắng mờ custom
      },
    },
  },
  safelist: [
    // Hero Section
    "h-[500px]",
    "md:h-[600px]",
    "absolute",
    "inset-0",
    "flex",
    "items-center",
    "w-full",
    "object-cover",
    "text-white",
    "text-4xl",
    "md:text-6xl",
    "font-bold",
    "mb-4",
    "text-xl",
    "md:text-2xl",
    "mb-6",
    "inline-block",
    "bg-white",
    "bg-black",
    "bg-opacity-10",
    "bg-opacity-20",
    "bg-opacity-30",
    "bg-opacity-40",
    "bg-opacity-50",
    "bg-opacity-60",
    "bg-opacity-70",
    "bg-opacity-80",
    "bg-opacity-90",
    "bg-opacity-100",
  ],
  plugins: [
    function ({ addUtilities }) {
      addUtilities({
        ".bg-hero-overlay": {
          "background-color": "rgba(255,255,255,0.2)",
        },
      });
    },
  ],
};
