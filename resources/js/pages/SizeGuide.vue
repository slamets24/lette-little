<script setup lang="ts">
import InfoPage from '@/components/lette/InfoPage.vue';
</script>

<template>
    <InfoPage eyebrow="FIT" title="Size Guide">
        <p>
            Sizes follow your baby's current height and weight, not age alone.
            If you are between two sizes, choose the larger one so the piece
            can be worn a little longer.
        </p>
        <table>
            <thead>
                <tr>
                    <th>Size</th>
                    <th>Height</th>
                    <th>Weight</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>0-3M</td>
                    <td>50–62 cm</td>
                    <td>3–6 kg</td>
                </tr>
                <tr>
                    <td>3-6M</td>
                    <td>62–68 cm</td>
                    <td>6–8 kg</td>
                </tr>
                <tr>
                    <td>6-9M</td>
                    <td>68–74 cm</td>
                    <td>8–9 kg</td>
                </tr>
                <tr>
                    <td>9-12M</td>
                    <td>74–80 cm</td>
                    <td>9–10 kg</td>
                </tr>
                <tr>
                    <td>12-18M</td>
                    <td>80–86 cm</td>
                    <td>10–12 kg</td>
                </tr>
            </tbody>
        </table>
        <p>
            These ranges are a starting point. Each product page lists the
            sizes actually in stock. For a second opinion, email
            <a href="mailto:hello@lettelittle.id">hello@lettelittle.id</a>
            with your baby's height, weight, and the piece you have in mind.
        </p>
    </InfoPage>
</template>
