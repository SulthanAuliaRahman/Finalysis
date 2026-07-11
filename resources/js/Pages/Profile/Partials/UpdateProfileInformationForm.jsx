import { Transition } from "@headlessui/react";
import { Link, useForm, usePage } from "@inertiajs/react";
import { Button } from "@/Components/ui/button";
import { Loader2, Save, User, Mail } from "lucide-react";

export default function UpdateProfileInformation({
    mustVerifyEmail,
    status,
    className = "",
}) {
    const user = usePage().props.auth.user;

    const { data, setData, patch, errors, processing, recentlySuccessful } =
        useForm({
            name: user.name,
            email: user.email,
        });

    const submit = (e) => {
        e.preventDefault();
        patch(route("profile.update"));
    };

    return (
        <section className={className}>
            <div className="mb-5">
                <h3 className="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <User className="w-4 h-4 text-blue-600" />
                    Informasi Profil
                </h3>
                <p className="text-xs text-slate-500 mt-0.5">
                    Perbarui nama dan alamat email akun Anda.
                </p>
            </div>

            <form onSubmit={submit} className="space-y-4">
                <div className="flex flex-col gap-1.5">
                    <label
                        className="text-xs font-semibold text-slate-700"
                        htmlFor="name"
                    >
                        Nama Lengkap
                    </label>
                    <input
                        id="name"
                        type="text"
                        value={data.name}
                        onChange={(e) => setData("name", e.target.value)}
                        className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
                        required
                        autoComplete="name"
                        disabled={processing}
                    />
                    {errors.name && (
                        <p className="text-xs text-red-500">{errors.name}</p>
                    )}
                </div>

                <div className="flex flex-col gap-1.5">
                    <label
                        className="text-xs font-semibold text-slate-700"
                        htmlFor="email"
                    >
                        Alamat Email
                    </label>
                    <input
                        id="email"
                        type="email"
                        value={data.email}
                        onChange={(e) => setData("email", e.target.value)}
                        className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
                        required
                        autoComplete="username"
                        disabled={processing}
                    />
                    {errors.email && (
                        <p className="text-xs text-red-500">{errors.email}</p>
                    )}
                </div>

                {mustVerifyEmail && user.email_verified_at === null && (
                    <div className="bg-amber-50 border border-amber-200 rounded-md p-3">
                        <p className="text-xs text-amber-800">
                            Email Anda belum diverifikasi.{" "}
                            <Link
                                href={route("verification.send")}
                                method="post"
                                as="button"
                                className="underline text-amber-700 hover:text-amber-900 font-semibold"
                            >
                                Kirim ulang email verifikasi.
                            </Link>
                        </p>

                        {status === "verification-link-sent" && (
                            <p className="mt-1.5 text-xs font-medium text-emerald-600">
                                Link verifikasi baru telah dikirim ke email
                                Anda.
                            </p>
                        )}
                    </div>
                )}

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
                                Simpan Profil
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
