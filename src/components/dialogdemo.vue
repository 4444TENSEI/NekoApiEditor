<template>
    <v-dialog max-width="400">
        <v-card class="pl-5 pr-5 pt-6 pb-5">

            <v-card-title class="pa-0 pb-3">
                <h3></h3>
            </v-card-title>

            <v-card-text class="pa-0">
                <p></p>
            </v-card-text>

            <v-input hide-details></v-input>

            <v-card-actions class="pl-6 pt-6 pr-0 pb-0">
                <v-btn color="info" rounded="pill" variant="tonal" size="large">确定</v-btn>
            </v-card-actions>

        </v-card>
        
        <v-snackbar location="top"></v-snackbar>
    </v-dialog>
</template>