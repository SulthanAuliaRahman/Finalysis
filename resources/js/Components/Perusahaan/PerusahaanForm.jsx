import { useEffect } from "react";
import { useForm } from "@inertiajs/react";
import { Button } from "@/Components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { DialogFooter } from "@/components/ui/dialog";
import { Loader2 } from "lucide-react";

const SEKTORS = ["Manufaktur", "Jasa", "Perdangan", "Lainnya"];

export default function PerusahaanForm({ editData, onClose }) {
    const { data, setData, post, put, processing, errors, reset } = useForm({
        nama: "",
        sektor: "",
        deskripsi: ""
    });

    // Sinkronisasi data jika sedang dalam mode Edit
    useEffect(() => {
        if (editData) {
            setData({
                nama: editData.nama ?? "",
                sektor: editData.sektor ?? "",
                deskripsi: editData.deskripsi ?? ""
            });
        } else {
            reset();
        }
    }, [editData]);

    function handleSubmit(e) {
        e.preventDefault();
        if (editData) {
            put(`/perusahaan/${editData.id}`, {
                onSuccess: () => { onClose(); reset(); }
            });
        } else {
            post("/perusahaan", {
                onSuccess: () => { onClose(); reset(); }
            });
        }
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-1.5">
                <Label htmlFor="nama">Nama Perusahaan <span className="text-red-500">*</span></Label>
                <Input
                    id="nama"
                    placeholder="contoh: PT Maju Bersama Tbk"
                    value={data.nama}
                    onChange={e => setData("nama", e.target.value)}
                    disabled={processing}
                />
                {errors.nama && <p className="text-xs text-red-500 mt-0.5">{errors.nama}</p>}
            </div>

            <div className="space-y-1.5">
                <Label htmlFor="sektor">Sektor Industri <span className="text-red-500">*</span></Label>
                <Select
                    value={data.sektor}
                    onValueChange={value => setData("sektor", value)}
                    disabled={processing}
                >
                    <SelectTrigger id="sektor">
                        <SelectValue placeholder="Pilih sektor..." />
                    </SelectTrigger>
                    <SelectContent>
                        {SEKTORS.map(s => <SelectItem key={s} value={s}>{s}</SelectItem>)}
                    </SelectContent>
                </Select>
                {errors.sektor && <p className="text-xs text-red-500 mt-0.5">{errors.sektor}</p>}
            </div>

            <div className="space-y-1.5">
                <Label htmlFor="deskripsi">Deskripsi Perusahaan</Label>
                <Textarea
                    id="deskripsi"
                    placeholder="Deskripsi singkat tentang profil perusahaan..."
                    value={data.deskripsi}
                    onChange={e => setData("deskripsi", e.target.value)}
                    rows={3}
                    className="resize-none"
                    disabled={processing}
                />
                {errors.deskripsi && <p className="text-xs text-red-500 mt-0.5">{errors.deskripsi}</p>}
            </div>

            <DialogFooter className="pt-2">
                <Button type="button" variant="outline" onClick={onClose} disabled={processing}>
                    Batal
                </Button>
                <Button type="submit" disabled={processing} className="min-w-[120px]">
                    {processing ? (
                        <><Loader2 className="w-4 h-4 animate-spin mr-2" /> Memproses</>
                    ) : (
                        editData ? "Simpan Perubahan" : "Tambah Perusahaan"
                    )}
                </Button>
            </DialogFooter>
        </form>
    );
}
