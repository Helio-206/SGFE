import * as React from "react";
import { cn } from "@/lib/utils";

export const Input = React.forwardRef<HTMLInputElement, React.InputHTMLAttributes<HTMLInputElement>>(
  ({ className, ...props }, ref) => (
    <input
      ref={ref}
      className={cn(
        "focus-ring h-10 w-full rounded-md border border-input bg-white px-3 text-sm text-institutional-ink shadow-line transition-colors placeholder:text-muted-foreground hover:border-institutional-blue/35",
        className
      )}
      {...props}
    />
  )
);
Input.displayName = "Input";
