import { useForm } from "@inertiajs/react";
import { useRef, useState } from "react";
import { Button } from "@/Components/ui/button";
import Modal from "@/Components/Modal";
import { Trash2, AlertTriangle, Loader2 } from "lucide-react";

export default function DeleteUserForm({ className = "" }) {
    const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
    const passwordInput = useRef();

    const {
        data,
        setData,
        delete: destroy,
        processing,
        reset,
        errors,
        clearErrors,
    } = useForm({
        password: "",
    });

    const confirmUserDeletion = () => {
        setConfirmingUserDeletion(true);
    };

    const deleteUser = (e) => {
        e.preventDefault();

        destroy(route("profile.destroy"), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        setConfirmingUserDeletion(false);
        clearErrors();
        reset();
    };

    return (
        <section className={className}>
            <div className="mb-5">
                <h3 className="text-sm font-bold text-red-700 flex items-center gap-2">
                    <AlertTriangle className="w-4 h-4 text-red-500" />
                    Zona Berbahaya
                </h3>
                <p className="text-xs text-slate-500 mt-0.5">
                    Setelah akun Anda dihapus, semua sumber daya dan datanya akan
                    dihapus secara permanen. Pastikan Anda telah mengunduh data
                    yang ingin disimpan.
                </p>
            </div>

            <Button
                variant="outline"
                onClick={confirmUserDeletion}
                className="text-red-600 border-red-200 hover:bg-red-50 hover:text-red-700 gap-1.5"
            >
                <Trash2 className="w-3.5 h-3.5" />
                Hapus Akun Saya
            </Button>

            <Modal show={confirmingUserDeletion} onClose={closeModal}>
                <form onSubmit={deleteUser} className="p-6 space-y-5">
                    <div className="flex items-start gap-3">
                        <div className="w-10 h-10 rounded-full bg-red-50 border border-red-100 flex items-center justify-center flex-shrink-0">
                            <AlertTriangle className="w-5 h-5 text-red-500" />
                        </div>
                        <div>
                            <h2 className="text-base font-bold text-slate-900">
                                Apakah Anda yakin ingin menghapus akun?
                            </h2>
                            <p className="mt-1 text-xs text-slate-500 leading-relaxed">
                                Setelah akun Anda dihapus, semua sumber daya dan
                                datanya akan dihapus secara permanen. Masukkan
                                kata sandi Anda untuk mengonfirmasi penghapusan
                                akun secara permanen.
                            </p>
                        </div>
                    </div>

                    <div className="flex flex-col gap-1.5">
                        <label
                            className="text-xs font-semibold text-slate-700"
                            htmlFor="delete_password"
                        >
                            Konfirmasi Kata Sandi
                        </label>
                        <input
                            id="delete_password"
                            type="password"
                            name="password"
                            ref={passwordInput}
                            value={data.password}
                            onChange={(e) =>
                                setData("password", e.target.value)
                            }
                            className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 bg-white"
                            placeholder="Masukkan kata sandi Anda..."
                            autoFocus
                        />
                        {errors.password && (
                            <p className="text-xs text-red-500">
                                {errors.password}
                            </p>
                        )}
                    </div>

                    <div className="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={closeModal}
                        >
                            Batal
                        </Button>

                        <Button
                            type="submit"
                            disabled={processing}
                            className="bg-red-600 hover:bg-red-700 text-white border-0 min-w-[140px]"
                        >
                            {processing ? (
                                <>
                                    <Loader2 className="w-4 h-4 animate-spin mr-1.5" />
                                    Menghapus...
                                </>
                            ) : (
                                <>
                                    <Trash2 className="w-4 h-4 mr-1.5" />
                                    Hapus Permanen
                                </>
                            )}
                        </Button>
                    </div>
                </form>
            </Modal>
        </section>
    );
}
