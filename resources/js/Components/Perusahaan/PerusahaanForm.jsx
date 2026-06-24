import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { DialogFooter } from "@/components/ui/dialog";

const SEKTORS = ["Manufaktur", "Teknologi", "Energi", "Keuangan", "Properti", "Retail", "Pertanian", "Kesehatan", "Lainnya"];

export default function PerusahaanForm({ initial, onSave, onClose }) {
    const [nama, setNama] = useState(initial?.nama ?? "");
    const [sektor, setSektor] = useState(initial?.sektor ?? "");
    const [deskripsi, setDeskripsi] = useState(initial?.deskripsi ?? "");
    const valid = nama.trim() && sektor;

    return (
        <div className="space-y-4">
            <div className="space-y-1.5">
                <Label>Nama Perusahaan <span className="text-red-500">*</span></Label>
                <Input
                    placeholder="contoh: PT Maju Bersama Tbk"
                    value={nama}
                    onChange={e => setNama(e.target.value)}
                />
            </div>
            <div className="space-y-1.5">
                <Label>Sektor Industri <span className="text-red-500">*</span></Label>
                <Select value={sektor} onValueChange={setSektor}>
                    <SelectTrigger>
                        <SelectValue placeholder="Pilih sektor..." />
                    </SelectTrigger>
                    <SelectContent>
                        {SEKTORS.map(s => <SelectItem key={s} value={s}>{s}</SelectItem>)}
                    </SelectContent>
                </Select>
            </div>
            <div className="space-y-1.5">
                <Label>Deskripsi</Label>
                <Textarea
                    placeholder="Deskripsi singkat tentang perusahaan..."
                    value={deskripsi}
                    onChange={e => setDeskripsi(e.target.value)}
                    rows={3}
                    className="resize-none"
                />
            </div>
            <DialogFooter>
                <Button variant="outline" onClick={onClose}>Batal</Button>
                <Button disabled={!valid} onClick={() => onSave({ nama: nama.trim(), sektor, deskripsi })}>
                    {initial?.id ? "Simpan Perubahan" : "Tambah Perusahaan"}
                </Button>
            </DialogFooter>
        </div>
    );
}
