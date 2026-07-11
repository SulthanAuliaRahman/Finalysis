import { Transition } from "@headlessui/react";
import { useForm } from "@inertiajs/react";
import { useRef } from "react";
import { Button } from "@/Components/ui/button";
import { Loader2, Save, Lock } from "lucide-react";

export default function UpdatePasswordForm({ className = "" }) {
    const passwordInput = useRef();
    const currentPasswordInput = useRef();

    const {
        data,
        setData,
        errors,
        put,
        reset,
        processing,
        recentlySuccessful,
    } = useForm({
        current_password: "",
        password: "",
        password_confirmation: "",
    });

    const updatePassword = (e) => {
        e.preventDefault();

        put(route("password.update"), {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errors) => {
                if (errors.password) {
                    reset("password", "password_confirmation");
                    passwordInput.current.focus();
                }

                if (errors.current_password) {
                    reset("current_password");
                    currentPasswordInput.current.focus();
                }
            },
        });
    };

    return (
        <section className={className}>
            <div className="mb-5">
                <h3 className="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <Lock className="w-4 h-4 text-amber-600" />
                    Ubah Kata Sandi
                </h3>
                <p className="text-xs text-slate-500 mt-0.5">
                    Pastikan akun Anda menggunakan kata sandi yang panjang dan acak
                    agar tetap aman.
                </p>
            </div>

            <form onSubmit={updatePassword} className="space-y-4">
                <div className="flex flex-col gap-1.5">
                    <label
                        className="text-xs font-semibold text-slate-700"
                        htmlFor="current_password"
                    >
                        Kata Sandi Saat Ini
                    </label>
                    <input
                        id="current_password"
                        ref={currentPasswordInput}
                        value={data.current_password}
                        onChange={(e) =>
                            setData("current_password", e.target.value)
                        }
                        type="password"
                        className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
                        autoComplete="current-password"
                        disabled={processing}
                    />
                    {errors.current_password && (
                        <p className="text-xs text-red-500">
                            {errors.current_password}
                        </p>
                    )}
                </div>

                <div className="flex flex-col gap-1.5">
                    <label
                        className="text-xs font-semibold text-slate-700"
                        htmlFor="password"
                    >
                        Kata Sandi Baru
                    </label>
                    <input
                        id="password"
                        ref={passwordInput}
                        value={data.password}
                        onChange={(e) => setData("password", e.target.value)}
                        type="password"
                        className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
                        autoComplete="new-password"
                        disabled={processing}
                    />
                    {errors.password && (
                        <p className="text-xs text-red-500">
                            {errors.password}
                        </p>
                    )}
                </div>

                <div className="flex flex-col gap-1.5">
                    <label
                        className="text-xs font-semibold text-slate-700"
                        htmlFor="password_confirmation"
                    >
                        Konfirmasi Kata Sandi Baru
                    </label>
                    <input
                        id="password_confirmation"
                        value={data.password_confirmation}
                        onChange={(e) =>
                            setData("password_confirmation", e.target.value)
                        }
                        type="password"
                        className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
                        autoComplete="new-password"
                        disabled={processing}
                    />
                    {errors.password_confirmation && (
                        <p className="text-xs text-red-500">
                            {errors.password_confirmation}
                        </p>
                    )}
                </div>

                <div className="flex items-center gap-3 pt-3 border-t border-slate-100">
                    <Button
                        type="submit"
                        disabled={processing}
                        className="min-w-[120px]"
                    >
                        {processing ? (
                            <>
                                <Loader2 className="w-4 h-4 animate-spin mr-1.5" />
                                Menyimpan
                            </>
                        ) : (
                            <>
                                <Save className="w-4 h-4 mr-1.5" />
                                Ubah Sandi
                            </>
                        )}
                    </Button>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <p className="text-xs text-emerald-600 font-semibold">
                            ✓ Tersimpan
                        </p>
                    </Transition>
                </div>
            </form>
        </section>
    );
}
