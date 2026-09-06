import { useForm, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import {
	ArrowLeft,
	BrainCircuit,
	Loader2,
	Plus,
	Save,
} from "lucide-react";

function SectionCard({ icon: Icon, title, description, children }) {
	return (
		<section className="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
			<div className="flex items-start gap-3 p-5 border-b border-slate-100 bg-slate-50/70">
				<div className="p-2 rounded-lg bg-blue-50 border border-blue-100 text-blue-700">
					<Icon className="w-4 h-4" />
				</div>
				<div className="min-w-0">
					<h3 className="text-sm font-bold text-slate-900">{title}</h3>
					<p className="text-xs text-slate-500 mt-0.5">{description}</p>
				</div>
			</div>

			<div className="p-5 space-y-4">{children}</div>
		</section>
	);
}

function Field({ label, htmlFor, children, hint, required = false, error }) {
	return (
		<div className="flex flex-col gap-1.5">
			<label className="text-xs font-semibold text-slate-700" htmlFor={htmlFor}>
				{label} {required && <span className="text-red-500">*</span>}
			</label>
			{children}
			{hint && <p className="text-[11px] text-slate-500">{hint}</p>}
			{error && <p className="text-xs text-red-500">{error}</p>}
		</div>
	);
}

export default function Edit({ configuration, mode }) {
	const isEdit = mode === "edit" || !!configuration?.id;

	const { data, setData, put, post, processing, errors } = useForm({
		name: configuration?.name ?? "",
		llm_provider: configuration?.llm_provider ?? "gemini",
		base_url: configuration?.base_url ?? "",
		llm_model: configuration?.llm_model ?? "",
		llm_api_key: "",
	});

	function handleSubmit(e) {
		e.preventDefault();
		if (isEdit) {
			put(`/settings/ai/${configuration.id}`);
		} else {
			post("/settings/ai");
		}
	}

	const isOllama = data.llm_provider === "ollama";

	return (
		<div className="max-w-5xl mx-auto space-y-4">
			<Link href="/settings/ai" className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
				<ArrowLeft className="w-3.5 h-3.5" /> Kembali ke Daftar Konfigurasi
			</Link>

			<form onSubmit={handleSubmit} className="space-y-5">
				<SectionCard
					icon={BrainCircuit}
					title={isEdit ? "Edit Konfigurasi AI" : "Tambah Konfigurasi AI Baru"}
					description={isEdit
						? "Perbarui pengaturan provider, model, dan API key untuk konfigurasi ini."
						: "Tambahkan konfigurasi provider AI baru ke dalam daftar."
					}
				>
					<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
						<div className="col-span-1 md:col-span-2">
							<Field label="Nama Konfigurasi" htmlFor="name" required hint="Beri nama untuk membedakan konfigurasi (misal: Gemini Flash Utama, OpenAI GPT-4o Cadangan)." error={errors.name}>
								<input
									id="name"
									type="text"
									value={data.name}
									onChange={e => setData("name", e.target.value)}
									placeholder="misal: Gemini Flash Utama"
									className="w-full px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
									disabled={processing}
								/>
							</Field>
						</div>

						<Field label="Provider" htmlFor="llm_provider" required hint="Pilih provider LLM yang ingin digunakan." error={errors.llm_provider}>
							<select
								id="llm_provider"
								value={data.llm_provider}
								onChange={e => setData("llm_provider", e.target.value)}
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
								disabled={processing}
							>
								<option value="gemini">Gemini (Google)</option>
								<option value="openai">OpenAI</option>
								<option value="anthropic">Anthropic (Claude)</option>
								<option value="ollama">Ollama (Local)</option>
							</select>
						</Field>

						<Field label="Model" htmlFor="llm_model" required hint="Nama model yang dipanggil backend (misal: gemini-2.0-flash, gpt-4o, llama3)." error={errors.llm_model}>
							<input
								id="llm_model"
								type="text"
								value={data.llm_model}
								onChange={e => setData("llm_model", e.target.value)}
								placeholder="misal: gemini-2.0-flash"
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
								autoComplete="new-password"
							/>
						</Field>

						{isOllama ? (
							<div className="col-span-1 md:col-span-2">
								<Field label="Base URL" htmlFor="base_url" required hint="Endpoint local server Ollama (default: http://localhost:11434)." error={errors.base_url}>
									<input
										id="base_url"
										type="text"
										value={data.base_url}
										onChange={e => setData("base_url", e.target.value)}
										placeholder="http://localhost:11434"
										className="w-full px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
										disabled={processing}
									/>
								</Field>
							</div>
						) : (
							<div className="col-span-1 md:col-span-2">
								<Field
									label="API Key"
									htmlFor="llm_api_key"
									required={!isEdit}
									hint={isEdit && configuration?.has_api_key
										? "API Key tersimpan. Biarkan kosong jika tidak ingin mengubah."
										: "Masukkan API key provider yang valid."
									}
									error={errors.llm_api_key}
								>
									<input
										id="llm_api_key"
										type="password"
										value={data.llm_api_key}
										onChange={e => setData("llm_api_key", e.target.value)}
										placeholder={isEdit && configuration?.has_api_key ? "••••••••••••••••" : "Masukkan API Key"}
										className="w-full px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-mono"
										disabled={processing}
									/>
								</Field>
							</div>
						)}
					</div>
				</SectionCard>

				{/* Submit */}
				<div className="flex flex-col sm:flex-row justify-end gap-2 pt-1">
					<Link href="/settings/ai">
						<Button type="button" variant="outline" disabled={processing} className="w-full sm:w-auto">
							Batal
						</Button>
					</Link>
					<Button type="submit" disabled={processing} className="w-full sm:w-auto min-w-[160px]">
						{processing ? (
							<><Loader2 className="w-4 h-4 animate-spin mr-1.5" /> Menyimpan</>
						) : isEdit ? (
							<><Save className="w-4 h-4 mr-1.5" /> Simpan Perubahan</>
						) : (
							<><Plus className="w-4 h-4 mr-1.5" /> Tambah Konfigurasi</>
						)}
					</Button>
				</div>
			</form>
		</div>
	);
}

Edit.layout = page => <AppLayout title="Konfigurasi AI" children={page} />;
