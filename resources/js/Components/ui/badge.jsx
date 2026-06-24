import * as React from "react";
import { Slot } from "@radix-ui/react-slot";
import { cva } from "class-variance-authority";
import { cn } from "@/lib/utils"; // Pastikan Anda memiliki file utils.js (clsx + twMerge)

const badgeVariants = cva(
    "inline-flex items-center justify-center rounded-md border px-2 py-0.5 text-xs font-medium w-fit whitespace-nowrap shrink-0 gap-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 transition-colors",
    {
        variants: {
            variant: {
                default: "border-transparent bg-slate-900 text-white hover:bg-slate-800",
                secondary: "border-transparent bg-slate-100 text-slate-900 hover:bg-slate-200",
                destructive: "border-transparent bg-red-500 text-white hover:bg-red-600",
                outline: "text-slate-950 border-slate-200 hover:bg-slate-100",
            },
        },
        defaultVariants: {
            variant: "default",
        },
    }
);

function Badge({ className, variant, asChild = false, ...props }) {
    const Comp = asChild ? Slot : "span";
    return (
        <Comp
            className={cn(badgeVariants({ variant }), className)}
            {...props}
        />
    );
}

export { Badge, badgeVariants };
