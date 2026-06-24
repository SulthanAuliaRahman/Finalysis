import { clsx } from "clsx"
import { twMerge } from "tailwind-merge"

// cn=ClassNameW
// ini agar daripada class inline  tailwind tidak terlalu panjang jadi bisa dilanjutkan dengan ,
// Example:
// className={cn(
//    "fixed max-sm:hidden top-5 right-5 z-50 p-2 rounded-full transition-colors duration-300",
//    "focus:outline-hidden"
// )}>

export const cn = (...inputs) =>{
    return twMerge(clsx(inputs));
}
