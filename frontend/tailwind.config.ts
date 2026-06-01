import type { Config } from "tailwindcss";
import tailwindcssAnimate from "tailwindcss-animate";

const config: Config = {
  darkMode: ["class"],
  content: [
    "./app/**/*.{ts,tsx}",
    "./components/**/*.{ts,tsx}",
    "./lib/**/*.{ts,tsx}"
  ],
  theme: {
    extend: {
      colors: {
        border: "hsl(var(--border))",
        input: "hsl(var(--input))",
        ring: "hsl(var(--ring))",
        background: "hsl(var(--background))",
        foreground: "hsl(var(--foreground))",
        surface: {
          DEFAULT: "hsl(var(--surface))",
          strong: "hsl(var(--surface-strong))",
          muted: "hsl(var(--surface-muted))"
        },
        primary: {
          DEFAULT: "hsl(var(--primary))",
          foreground: "hsl(var(--primary-foreground))"
        },
        muted: {
          DEFAULT: "hsl(var(--muted))",
          foreground: "hsl(var(--muted-foreground))"
        },
        destructive: {
          DEFAULT: "hsl(var(--destructive))",
          foreground: "hsl(var(--destructive-foreground))"
        },
        institutional: {
          ink: "#111318",
          blue: "#12355B",
          deep: "#071B33",
          gold: "#C9A227",
          red: "#B11226",
          mist: "#EEF3F8",
          line: "#D8DEE8",
          teal: "#0E6B6B"
        }
      },
      borderRadius: {
        lg: "8px",
        md: "6px",
        sm: "4px"
      },
      boxShadow: {
        institutional: "0 18px 44px rgba(7, 27, 51, 0.10)",
        line: "0 1px 0 rgba(17, 19, 24, 0.08)",
        quiet: "0 12px 32px rgba(8, 26, 45, 0.07)"
      },
      fontFamily: {
        sans: ["Inter", "ui-sans-serif", "system-ui", "sans-serif"]
      }
    }
  },
  plugins: [tailwindcssAnimate]
};

export default config;
