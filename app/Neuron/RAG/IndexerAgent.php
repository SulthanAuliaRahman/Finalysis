<?php

namespace App\Neuron\RAG;

class IndexerAgent extends BaseRagAgent
{
    // pada data loader butuh indexer untuk memproses dokumen dan membuat vektor embedding
    // jadi ini seharus nya tidak terpakai sebagai agent analisis
    // mungkin nanti kepikiran solusi yang lebih baik(cleaner) untuk sekarang gini dulu aja

    // kenapa gak BaseRagAgent dijadiini?
    // karena BaseRagAgent itu untuk sebagai blueprint pembuatan agent (abstract class) jadi harus nya jangan di instansiasi
    // atau mungkin bagus nya baseRagAgent bukan abstract class? ah bingung
}
