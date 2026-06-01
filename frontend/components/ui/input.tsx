import * as React from "react";
import { cn } from "@/lib/utils";

export const Input = React.forwardRef<HTMLInputElement, React.InputHTMLAttributes<HTMLInputElement>>(
  ({ className, ...props }, ref) => (
    <input
      ref={ref}
      className={cn(
        "focus-ring h-10 w-full rounded-md border border-input bg-surface-strong/90 px-3 text-sm text-institutional-ink shadow-line transition-colors placeholder:text-muted-foreground hover:border-institutional-blue/40 disabled:cursor-not-allowed disabled:bg-surface-muted disabled:text-muted-foreground",
        className
      )}
      {...props}
    />
  )
);
Input.displayName = "Input";
