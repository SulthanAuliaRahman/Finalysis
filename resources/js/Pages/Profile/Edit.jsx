import AppLayout from "@/Layouts/AppLayout";
import DeleteUserForm from "./Partials/DeleteUserForm";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm";

export default function Edit({ mustVerifyEmail, status }) {
    return (
        <div className="max-w-2xl mx-auto space-y-6">
            <div>
                <h2 className="text-xl font-bold text-slate-900">Pengaturan Profil</h2>
                <p className="text-xs text-slate-500 mt-0.5">
                    Kelola informasi akun, keamanan kata sandi, dan zona berbahaya.
                </p>
            </div>

            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs">
                <UpdateProfileInformationForm
                    mustVerifyEmail={mustVerifyEmail}
                    status={status}
                />
            </div>

            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs">
                <UpdatePasswordForm />
            </div>

            <div className="bg-white border border-red-100 rounded-xl p-6 shadow-xs">
                <DeleteUserForm />
            </div>
        </div>
    );
}

Edit.layout = (page) => <AppLayout title="Pengaturan Profil" children={page} />;
